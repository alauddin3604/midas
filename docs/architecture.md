# Architecture

Midas is an API-only backend for the IT department's ticketing system, consumed by a separate SPA. It
separates **what the business does** (Domain) from **how it is delivered** (App). The split is enforced
by architecture tests in `tests/Arch`, so a violation fails the suite.

## Layers

Dependencies point one way only:

    App → Domain → Support

(`App` may use anything; `Domain` may use `Support`; `Support` uses nothing of ours.)

| Layer | Namespace | Responsibility |
|---|---|---|
| App | `App\` | Wiring and delivery. Wiring: bootstrap, service providers, route loading, exception-to-response handling, adapters between domains. Delivery: HTTP (controllers, requests, resources, middleware), console commands, listeners, jobs. Turns input into Payloads, calls one Action, formats output. |
| Domain | `Domain\<Domain>\` | Business rules and data. Knows nothing about HTTP, sessions, queues or the console. |
| Support | `Support\` | Infrastructure with no business meaning: base classes, providers, utils. |

### Rules

**Domain**
- May use Eloquent, `Illuminate\Support` and Support classes.
- Never imports `App`, `Illuminate\Http`, `Illuminate\Routing`, `Illuminate\Console` or the `Auth`/`Request` facades.
- Never dispatches jobs or touches listeners. Actions only fire Events.
- Exceptions extend a category from `Support\Bases\Exceptions` (which must ultimately extend
  `DomainException`, the type the HTTP handler renders) and never carry HTTP status codes.

**App**
- Controllers, commands, listeners and jobs contain no business rules; they call one Action.
- Session and auth mechanics (login, logout) live in controllers. Actions never touch the session.
- Controllers decide which relations to eager-load for a response. Actions don't shape data for one
  particular response.

**Support**
- Never imports `Domain` or `App`.
- `Bases/` holds classes to extend.

**Cross-domain**
- Reference other domains by ID only (`requester_id`, `assignee_id`). No Eloquent relations to another
  domain's Models.
- A domain may use another domain's Actions, Payloads, Enums and ValueObjects.
- For lookups and capabilities ("may this user assign tickets?"), the consuming domain declares a
  **contract** in its own `Contracts/`. An adapter in `app/Adapters/<Domain>/` implements it using the
  other domain, and a service provider binds it. Neither domain imports the other.
- Cross-domain side effects go through Events. The reacting domain handles them in a Listener under App,
  which calls an Action.

### Where things go

| Thing | Location |
|---|---|
| HTTP, versioned | `app/Http/V1/{Controllers,Requests,Resources}/<Domain>/` |
| Route files | `routes/api/v1/<domain>.php` |
| Global middleware | `app/Middleware/` |
| Domain exception → HTTP status map | `app/Exceptions/Domain/` |
| Cross-domain adapters | `app/Adapters/<Domain>/` |
| Policies, contracts, casts | `src/Domain/<Domain>/{Policies,Contracts,Casts}/` |

## Building blocks

| Block | Location | Shape |
|---|---|---|
| Action | `Domain/<D>/Actions` | `final readonly`, suffix `Action`, one public `execute()`. The only public write API of a domain. Owns the transaction (nested Actions join it), locks rows it read-modify-writes with `lockForUpdate()`, and fires Events. |
| Payload | `Domain/<D>/Payloads` | `final readonly` typed DTO, suffix `Payload`. Includes the acting user's id (`actorId`) when the operation needs one. |
| Model | `Domain/<D>/Models` | Data access, relations, casts, and guards for its own invariants (`Ticket::changeStatus()`, `Ticket::assignTo()`): pure state changes that throw domain exceptions. No persistence, transactions, events or orchestration across models; that is the Action's job. |
| Query builder | `Domain/<D>/QueryBuilders` | `final`, `<Model>QueryBuilder`; every scope lives here, attached with `#[UseEloquentBuilder]`. |
| ValueObject | `Domain/<D>/ValueObjects` | `final readonly`, private constructor, named constructors (`generate()`, `fromString()`), validates itself, has `equals()`. Persisted through a cast in `Casts/`. |
| Event | `Domain/<D>/Events` | `final readonly`, extends `DomainEvent` (dispatched after commit). A past-tense fact carrying the aggregate id plus a snapshot of primitives, enums and `DateTimeImmutable` (`occurredAt`). Never an Eloquent model. Fire with `event(new X(...))`. |
| Exception | `Domain/<D>/Exceptions` | `final`, named for the situation (`EmailAlreadyRegistered`, `InvalidStatusTransition`). |
| Enum | `Domain/<D>/Enums` | Backed, PascalCase cases. May hold the domain's rules about itself (`TicketStatus::allowedTransitions()`). |
| Contract | `Domain/<D>/Contracts` | Interface for something the domain needs from outside (`TicketingAccess`). |
| Policy | `Domain/<D>/Policies` | Record-level authorization. Depends on contracts, never on another domain's internals. |

**Reads** may use a model's query builder directly in a controller. **Writes must go through an
Action.** Extract a query object only when a read gains real rules or a second consumer, not by default.

## Flow of an operation

```
Request ──► FormRequest (authorize + validate) ──► payload()
                                                      │
                                                      ▼
Resource ◄── Controller ◄── Action::execute() ──► Model / ValueObject
                                  │
                                  ├── throws DomainException ──► mapped to HTTP status in App
                                  └── event(new DomainEvent) ──► Listener (App) does I/O
```

Example, user registration:

1. `RegisterRequest::payload()` builds a `RegisterUserPayload`.
2. `RegisterController` calls `RegisterUserAction::execute($payload)`.
3. The action throws `EmailAlreadyRegistered` on a duplicate, otherwise creates the user and fires
   `UserRegistered`.
4. The controller returns a `UserResource`.

Example, assigning a ticket:

1. `AssignTicketRequest::authorize()` checks the `assign` policy; `payload()` builds an
   `AssignTicketPayload`. The actor comes from `$this->user()`, never from client input.
2. `AssignTicketController` calls `AssignTicketAction::execute($payload)`.
3. In one transaction, the action locks the ticket, calls `Ticket::assignTo()` (which throws
   `TicketAlreadyClosed` for a closed ticket and moves `New` to `Assigned`), saves it, records a status
   history entry through `CreateTicketStatusHistoryAction` when the status changed, and fires
   `TicketAssigned` after commit.
4. The controller loads `statusHistories` and returns a `TicketResource`.

Actions never read `Auth`. The acting user arrives as an argument or as `actorId` in the Payload.

## Errors

Actions throw domain exceptions; the Domain never decides on a status code. Each domain has a mapper
(`App\Exceptions\Domain\<Domain>ExceptionsMapper`, implementing `DomainExceptionsMapper`) registered in
`DomainExceptionsRegistry`. The handlers in `bootstrap/app.php` turn exceptions into JSON through
`ApiErrorResponse`:

- A mapped `DomainException` uses its mapped status. An unmapped one returns 422.
- Domain exceptions are not reported (`dontReport`); they are expected outcomes.
- Validation failures return 422 with `errors: [{field, error}]`. Unauthenticated returns 401, and a
  CSRF mismatch (419) and other 4xx `HttpException`s keep their status.
- Any other error returns a generic 500, except when `app.debug` is on, where Laravel's own output is
  used.

| Exception | Status |
|---|---|
| `EmailAlreadyRegistered` | 409 |
| `InvalidCredentials` | 401 |
| `InvalidPasswordResetToken` | 422 |
| `CannotModifyOwnAccount` | 403 |
| `InvalidStatusTransition` | 409 |
| `TicketAlreadyClosed` | 409 |
| `TicketNotAssigned` | 422 |

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

Capabilities are `Permission` enum cases, mapped to roles in `Role::permissions()`:

| Permission | Admin | Member |
|---|---|---|
| `ManageUsers` | yes | no |

`IdentityServiceProvider` registers every permission as a Gate ability. Pass the enum itself, never
`->value`:

```php
Route::middleware(Authorize::using(Permission::ManageUsers));
Gate::authorize(Permission::ManageUsers);
```

Record-level rules go in `Domain/<Domain>/Policies` (discovered through the `Models` → `Policies`
sibling convention; register explicitly in the domain's provider if the namespace doesn't follow it).
Form requests call them from `authorize()`. Invariants stay in Models and Actions as domain exceptions.

When a policy needs an answer owned by another domain, it asks a contract instead of importing it:
`TicketPolicy` depends on `TicketingAccess`, and `App\Adapters\Ticketing\IdentityTicketingAccess`
implements it with Identity permissions.

## Routing

`routes/api.php` is loaded with `withRouting(api: ..., apiPrefix: '')` and scans
`routes/api/<version>/*.php`:

| File | Prefix | Name prefix |
|---|---|---|
| `routes/api/v1/*.php` | `v1` | `v1.` |

Route files declare their own `auth:sanctum` / `Authorize` middleware. HTTP code is version-first
(`app/Http/V1/...`). A new version only gets classes for endpoints whose contract changes, and every
version calls the same Actions.

## Tests

Tests mirror `src/` and `app/`, split by whether they need the database:

| Kind | Location |
|---|---|
| Actions (database) | `tests/Feature/Domain/<D>/Actions/` |
| Pure logic: ValueObjects, Enums, Policies with a fake contract | `tests/Unit/Domain/<D>/...` |
| HTTP endpoints | `tests/Feature/Http/V1/<D>/` |
| Layer rules | `tests/Arch/` |

Test every Action directly (including the invalid-transition matrix for status changes, with the valid
transitions written out by hand so the test is a spec, not a copy of the enum). Test controllers through
HTTP, which also covers their requests, resources, policies and routes. Shared helpers live in
`tests/Helpers/` and are loaded from `tests/Pest.php`.