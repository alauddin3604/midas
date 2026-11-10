# Architecture

Midas is an API-only backend for a stall ordering system (cashiers take customer orders), consumed by a
separate SPA. It uses a conventional Laravel (MVC) layout under `app/`, with each layer folder grouped by
**context** (`Identity` today). The conventions are not documented hope: they are enforced by
architecture tests in `tests/Arch`, so a violation fails the suite.

## Layers

Everything lives under `App\`. Dependencies point one way only:

    Delivery → Business → Support

| Layer | Namespaces | Responsibility |
|---|---|---|
| Delivery | `App\Http`, `App\Console`, `App\Providers`, `App\Exceptions\Domain` | How the app is reached and wired: controllers, form requests, resources, console commands, service providers, exception-to-response mapping. Turns input into Payloads, calls one Action, formats output. |
| Business | `App\Actions`, `App\Models`, `App\QueryBuilders`, `App\ValueObjects`, `App\Payloads`, `App\Events`, `App\Enums`, `App\Exceptions\<Context>`, `App\Policies` | Business rules and data. Knows nothing about HTTP, sessions or the console. |
| Support | `App\Support` | Infrastructure with no business meaning: small utilities and helpers. |

Each layer folder is split by context, for example `App\Actions\Identity` and `App\Events\Identity`.
A context is the equivalent of a bounded context: it is a naming and boundary rule, not a separate source tree.

### Contexts

| Context | Purpose |
|---|---|
| `Identity` | Users, authentication, password reset, roles and permissions. |

### Rules

**Business layer**
- May use Eloquent, `Illuminate\Support` and Support classes.
- Never imports `App\Http`, `App\Console`, `Illuminate\Http`, `Illuminate\Routing` or `Illuminate\Console`.
- Never uses the `Auth` or `Request` facades. Actions receive the acting user as an argument or as
  `actorId` in a Payload.
- Models and query builders never call Actions.
- Exceptions are named for the situation and never carry HTTP status codes.

**Delivery layer**
- Controllers, commands and listeners contain no business rules; they call one Action.
- Session and auth mechanics (login, logout) live in controllers. Actions never touch the session.
- Controllers decide which relations to eager-load for a response. Actions don't shape data for one
  particular response.

**Support**
- Never imports other `App\` layers.

**Cross-context**
- Reference another context by ID only (`user_id`, `requester_id`). No Eloquent relations to another
  context's Models.
- A context may use another context's Actions, Payloads, Enums and ValueObjects.
- When one context needs a capability from another ("may this user do X?"), the consuming context declares
  a **contract** (`app/Contracts/<Context>/`), an adapter (`app/Adapters/<Context>/`) implements it using
  the other context, and a service provider binds it. Neither context imports the other. Neither folder
  exists yet; create them when the first real case appears.
- Cross-context side effects go through Events. The reacting context handles them in a Listener, which
  calls an Action.

### Where things go

| Thing | Location |
|---|---|
| Controllers | `app/Http/Controllers/V1/<Context>/` (created with `php artisan make:controller V1/<Context>/*Controller --invokable`) |
| Form requests | `app/Http/Requests/V1/<Context>/` |
| API resources | `app/Http/Resources/V1/<Context>/` |
| Route files | `routes/api/v1/<context>.php` |
| Exceptions extending `App\Exceptions\MidasApiException` | `app/Exceptions/<Context>/` |
| Policies | `app/Policies/<Context>/` |
| Console commands | `app/Console/Commands/<Context>/` |
| Service providers | `app/Providers/` (one per context, e.g. `IdentityServiceProvider`) |

## Building blocks

| Block | Location | Shape |
|---|---|---|
| Action | `app/Actions/<C>` | `final readonly`, suffix `Action`, one public `execute()`. The only public write API of a context. Owns the transaction (nested Actions join it), locks rows it read-modify-writes with `lockForUpdate()`, and fires Events. Never uses the `Request` object. |
| Payload | `app/Payloads/<C>` | `final readonly` typed DTO, suffix `Payload`. Includes the acting user's id (`actorId`) when the operation needs one. |
| Model | `app/Models/<C>` | Eloquent model. Data access, relations, casts, and guards for its own invariants: pure state changes that throw exceptions. No persistence, transactions, events or orchestration across models; that is the Action's job. Never uses Actions. |
| Query builder | `app/QueryBuilders/<C>` | `final`, extends Eloquent's `Builder`, suffix `QueryBuilder`; every scope lives here, attached with `#[UseEloquentBuilder]`. |
| ValueObject | `app/ValueObjects/<C>` | `final readonly`, validates itself, has `equals()`. Use named constructors where construction has more than one meaning. |
| Event | `app/Events/<C>` | Extends `App\Events\Event`. A past-tense fact carrying the aggregate id plus a snapshot of primitives, enums and `DateTimeImmutable`. Never an Eloquent model. Fire with `event(new X(...))`. |
| Exception | `app/Exceptions/<C>` | Named for the situation (`InvalidCredentials`, `CannotModifyOwnAccount`). |
| Enum | `app/Enums/<C>` | Backed enum, PascalCase cases. May hold rules about itself (`Role::permissions()`). |
| Policy | `app/Policies/<C>` | Record-level authorization. Depends on contracts, never on another context's internals. |

**Reads** may use a model's query builder directly in a controller. **Writes must go through an
Action.** Extract a query object only when a read gains real rules or a second consumer, not by default.

## Flow of an operation

```
Request ──► FormRequest (authorize + validate) ──► payload()
                                                      │
                                                      ▼
Resource ◄── Controller ◄── Action::execute() ──► Model / ValueObject
                                  │
                                  ├── throws exception ──► mapped to HTTP status (App\Exceptions\Domain)
                                  └── event(new Event) ──► Listener does I/O
```

Example, user registration:

1. `RegisterRequest::payload()` builds a `RegisterUserPayload`.
2. `RegisterController` calls `RegisterUserAction::execute($payload)`.
3. The action creates the user and fires `UserRegistered`.
4. The controller returns a `UserResource`.

Actions never read `Auth`. The acting user arrives as an argument or as `actorId` in the Payload.

## Errors

Expected failures are exceptions that extend `App\Exceptions\MidasApiException`. Each exception lives in `app/Exceptions/<Context>/`, is named for the situation, and passes its own HTTP status code to the base class constructor. The base class's `render()` turns it into JSON through `App\Support\Utils\ApiErrorResponse`, so Laravel renders it without any handler registered in bootstrap/app.php`.

- The status defaults to 400 when an exception doesn't pass one.
- These exceptions are not reported (`dontReport`); they are expected outcomes.
- Exceptions that aren't `MidasApiException` (validation, authentication, `HttpException`s such as a CSRF mismatch, unexpected errors) go through Laravel's normal handling in `bootstrap/app.php`.
- Any other error returns a generic 500, except when `app.debug` is on, where Laravel's own output is used.

| Exception | Status |
|---|---|
| `InvalidCredentials` | 401 |
| `InvalidPasswordResetToken` | 422 |
| `CannotModifyOwnAccount` | 403 |

## Authentication

The SPA authenticates with Sanctum's cookie-based SPA flow, not tokens:

1. `GET /sanctum/csrf-cookie`, then `POST /v1/login` with the `X-XSRF-TOKEN` header and
   `credentials: 'include'`.
2. `LoginController` calls `AuthenticateUserAction` (credentials and events only), then establishes the
   session itself: `Auth::guard('web')->login($user)` and `$request->session()->regenerate()`.
3. `LogoutController` logs out, invalidates the session and regenerates the CSRF token.

Setup: `$middleware->statefulApi()`, `SANCTUM_STATEFUL_DOMAINS`, `SESSION_DOMAIN`, and CORS with
`supports_credentials` for the `v1/*` and `sanctum/csrf-cookie` paths. Routes use `auth:sanctum`, which
also accepts bearer tokens if a non-browser client is ever needed. The SPA and API must share a
registrable domain, or the SPA must proxy API calls through its own origin.

## Authorization

Capabilities are `App\Enums\Identity\Permission` cases, mapped to roles in `Role::permissions()`:

| Permission | Admin | Member |
|---|---|---|
| `ManageUsers` | yes | no |

`IdentityServiceProvider` registers every permission as a Gate ability. Pass the enum itself, never
`->value`:

```php
Route::middleware(Authorize::using(Permission::ManageUsers));
Gate::authorize(Permission::ManageUsers);
```

Record-level rules go in `app/Policies/<Context>` (discovered through Laravel's `Models` → `Policies`
namespace convention, e.g. `App\Models\Identity\User` → `App\Policies\Identity\UserPolicy`; register
explicitly in the context's provider if the namespace doesn't follow it). Form requests call them from
`authorize()`. Invariants stay in Models and Actions as exceptions.

When a policy needs an answer owned by another context, it asks a contract instead of importing it.

## Routing

`routes/api.php` is loaded with `withRouting(api: ..., apiPrefix: '')` and scans
`routes/api/<version>/*.php`:

| File | Prefix | Name prefix |
|---|---|---|
| `routes/api/v1/*.php` | `v1` | `v1.` |

Route files declare their own `auth:sanctum` / `Authorize` middleware. HTTP code is versioned by folder
(`Controllers/V1`, `Requests/V1`, `Resources/V1`). A new version only gets classes for endpoints whose
contract changes, and every version calls the same Actions.

## Architecture tests

`tests/Arch` is the source of truth for the rules above. It combines Pest's presets with project rules:

- **Presets:** `arch()->preset()->php()`, `->security()` and `->strict()`. These cover strict types, no
  debugging leftovers (`dd`, `dump`, `ray`, ...), no dangerous functions (`eval`, `exec`, ...), and final
  classes. `strict()` ignores `ApiController`, `Event` (abstract base classes) and `App\Models` (Laravel's
  `casts()` is protected, which `strict()` forbids). Models get their own final and strict types rules
  instead.
- **Shape rules per block:** Actions, Payloads, Query builders, Controllers, Requests, Commands, Enums and
  Models, as in the table above.
- **Layer rules:** business layers do not use `App\Http` / `App\Console` / `Illuminate\Http` /
  `Illuminate\Routing` / `Illuminate\Console`; `Support` does not use other `App\` layers; Actions never
  use `Request`; Models and query builders never use Actions.

When a rule fails, fix the code. Add to `ignoring()` only for a genuine base class or a framework
convention, and say why in a comment.

## Tests

Tests mirror `app/`, split by whether they need the database:

| Kind | Location |
|---|---|
| Actions (database) | `tests/Feature/Actions/<C>/` |
| Pure logic: ValueObjects, Enums, Policies with a fake contract | `tests/Unit/<Layer>/<C>/` |
| HTTP endpoints | `tests/Feature/Http/V1/<C>/` |
| Layer rules | `tests/Arch/` |

Test every Action directly. Test controllers through HTTP, which also covers their requests, resources,
policies and routes. Shared helpers live in `tests/Helpers/` and are loaded from `tests/Pest.php`.