# Architecture

Midas separates **what the business does** (Domain) from **how it is delivered** (Interfaces). The split
is enforced by architecture tests in `tests/Arch`, so a violation fails the suite.

## Layers

Dependencies point one way only:

    App → Interfaces → Domain → Support

(`App` may use anything; `Interfaces` may use `Domain` and `Support`; `Domain` may use `Support`;
`Support` uses nothing of ours.)

| Layer | Namespace | Responsibility |
|---|---|---|
| App | `App\` | Wiring only: bootstrap, service providers, route and command discovery, exception-to-response handlers, schedule. |
| Interfaces | `Interfaces\<Domain>\` | Delivery: HTTP (routes, controllers, requests, resources, middleware), Livewire, ViewModels, console, listeners, jobs, per-domain providers (e.g. Gate abilities). Turns input into Payloads, calls one Action, formats output. |
| Domain | `Domain\<Domain>\` | Business rules and data. Knows nothing about HTTP, Livewire, queues or the console. |
| Support | `Support\` | Infrastructure with no business meaning: base classes, providers, utils. |

### Rules:

**Domain**
- May use Eloquent, `Illuminate\Support` and Support classes.
- Never imports `App`, `Interfaces`, Livewire, `Illuminate\Http`, `Illuminate\Routing`, `Illuminate\Console` or the `Auth`/`Request` facades.
- Never dispatches jobs or touches listeners. Actions only fire Events.
- Exceptions extend a category from `Support\Bases\Exceptions` and never carry HTTP status codes.

**Interfaces**
- Never imports `App`.
- Controllers, Livewire components, commands, listeners and jobs contain no business rules; they call one Action.
- One domain's Interfaces never import another's, except `Midas`, which nothing may import.

**Support**
- Never imports `Domain`, `Interfaces` or `App`.
- `Bases/` holds classes to extend; `Http/` holds helpers to use or compose.

**Cross-domain**
- A domain may use another domain's Actions, Payloads, Enums and ValueObjects, but never its Models (other than holding an ID).
- Cross-domain side effects go through Events. The reacting domain handles them in a Listener under its own `Interfaces/<Domain>/Listeners/`, which calls an Action.

### Where things go

| Thing | Location |
|---|---|
| Global middleware | `Interfaces/Midas/Http/Middleware/` |
| Surface-specific middleware | `Interfaces/<Domain>/Http/<Surface>/Middleware/` |
| Route files | `Interfaces/<Domain>/Http/<Surface>/Routes/` |
| Domain exception → HTTP status map | `App/Exceptions/` |

### Midas

`Interfaces/Midas` is the application shell (home page, layout, global middleware). It has no matching `Domain/Midas`. Business auth (login, reset) belongs to `Identity`, not `Midas`.

## Building blocks

| Block | Location | Shape |
|---|---|---|
| Action | `Domain/<D>/Actions` | `final readonly`, suffix `Action`, one public `execute()`. The only public write API of a domain. |
| Payload | `Domain/<D>/Payloads` | `final readonly` typed DTO, suffix `Payload`. |
| Model | `Domain/<D>/Models` | Data access, relations, casts. No business operations. |
| Query builder | `Domain/<D>/QueryBuilders` | `final`, `<Model>QueryBuilder`; every scope lives here, attached with `#[UseEloquentBuilder]`. |
| Event | `Domain/<D>/Events` | `final readonly`, extends `DomainEvent` (dispatched after commit). Fire with `event(new X(...))`. |
| Exception | `Domain/<D>/Exceptions` | `final`, extends `DomainException`, named for the situation (`EmailAlreadyRegistered`). |
| Enum | `Domain/<D>/Enums` | Backed, PascalCase cases. |

Reads may use a model's query builder directly from the App layer. **Writes must go through an
Action.**

## Flow of an operation

```
Request / Livewire form
   │ validates
   ▼
Payload ──► Action::execute() ──► Model / ValueObject ──► Resource / View
                 │
                 ├── throws DomainException  ──► mapped to HTTP status in App
                 └── event(new DomainEvent)  ──► Listener (App) does I/O
```

Example, user registration:

1. `RegisterRequest::payload()` builds a `RegisterUserPayload`.
2. `RegisterController` calls `RegisterUserAction::execute($payload)`.
3. The action throws `EmailAlreadyRegistered` on a duplicate, otherwise creates the user and fires
   `UserRegistered`.
4. The controller returns a `UserResource`.

Actions receive the acting user as an argument (see `UpdateUserAction::execute($actor, ...)`) and
never read `Auth`.

## Errors

Actions throw domain exceptions; the Domain never decides on a status code. Each domain's service
provider tags an exception map implementing `Support\Contracts\MapsDomainExceptions`
(e.g. `IdentityExceptionMap`), and `Support\Exceptions\DomainExceptionRenderer` turns a matching
exception into a JSON response for requests that expect JSON.

| Exception | Status |
|---|---|
| `EmailAlreadyRegistered` | 409 |
| `InvalidCredentials` | 401 |
| `InvalidPasswordResetToken` | 422 |
| `CannotModifyOwnAccount` | 403 |

## Authorization

Capabilities are `Permission` enum cases, mapped to roles in `Role::permissions()`:

| Permission | Admin | Member |
|---|---|---|
| `AccessDashboard` | yes | yes |
| `ManageUsers` | yes | no |
| `ViewAuditLog` | yes | no |

`IdentityServiceProvider` registers every permission as a Gate ability. Pass the enum itself, never
`->value`:

```php
Route::middleware(Authorize::using(Permission::ManageUsers));
$this->authorize(Permission::ManageUsers);
```

```blade
@can(Permission::ManageUsers) ... @endcan
```

Record-level rules go in `Domain/<Domain>/Policies` (auto-discovered); invariants stay in Actions
as domain exceptions.

## Events and the Audit domain

Identity actions fire events (`UserRegistered`, `UserUpdated`, `UserDeleted`, `UserAuthenticated`,
`UserAuthenticationFailed`, `PasswordResetLinkRequested`, `PasswordReset`).
`Interfaces\Audit\Listeners\RecordIdentityActivity` is the only class that knows both domains: it
translates each event into a `RecordAuditEntryPayload` and calls `RecordAuditEntryAction`.

- The `Audit` domain is generic and may not use `Domain\Identity` (arch-tested).
- `CaptureAuditContext` middleware stores IP, user agent and URL in hidden `Context`, so listeners
  never read the request.
- Recording never breaks the audited operation: failures are reported and swallowed.
- Entries are append-only and pruned daily after `AUDIT_RETENTION_DAYS`.

## Routing

Route files are auto-discovered by `App\Routing\DomainRouteLoader`:

| File | Prefix | Name prefix |
|---|---|---|
| `src/Interfaces/<D>/Http/Dashboard/Routes/*.php` | none | none |
| `src/Interfaces/<D>/Http/Api/V1/Routes/*.php` | `api/v1` | `api.v1.` |

Route files declare their own `auth` / `guest` / `Authorize` middleware. `App\Midas` is reserved
for the cross-domain app shell (the dashboard page and the `/` redirect).

## Tests

`tests/App`, `tests/Domain`, `tests/Interfaces` and `tests/Support` mirror `src/`; `tests/Arch` holds the layer rules.
Test every Action directly. Test controllers and Livewire pages through HTTP/Livewire, which also
covers their requests, resources and routes.
