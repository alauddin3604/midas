## Application Structure & Architecture

### 1. Where Things Live

Everything lives under `app/` in the standard Laravel layout. Each layer folder is split by **context** (one folder per business capability, e.g. `Identity`): `app/Actions/Identity/`, `app/Events/Identity/`, and so on. Business code is grouped by type, not by a separate `src/Domain` tree.

```
app/
├── Actions/<Context>/             ← Business operations (the only public write API)
├── Console/
│   └── Commands/<Context>/        ← Console commands
├── Enums/<Context>/               ← Backed enums (e.g. Role, Permission)
├── Events/
│   ├── Event.php                  ← Abstract base class for events
│   └── <Context>/
├── Exceptions/
│   ├── MidasApiException.php      ← Base class for all expected API failures
│   └── <Context>/                 ← Exceptions named for the situation
├── Http/
│   ├── Controllers/V1/
│   │   ├── ApiController.php      ← Abstract base class for controllers
│   │   └── <Context>/
│   ├── Requests/V1/<Context>/
│   └── Resources/V1/<Context>/
├── Jobs/<Context>/                ← Created when needed
├── Listeners/<Context>/           ← Created when needed
├── Models/<Context>/
├── Payloads/<Context>/
├── Policies/<Context>/
├── Providers/                     ← One <Context>ServiceProvider per context: Gate abilities, bindings
├── QueryBuilders/<Context>/       ← <Model>QueryBuilder: every query scope of a model
├── Support/
│   └── Utils/                     ← Utils / helpers (e.g. ApiErrorResponse)
└── ValueObjects/<Context>/
```

### 2. Layers and Dependency Rules

| Layer | Namespaces | Responsibility |
|---|---|---|
| Delivery | `App\Http`, `App\Console`, `App\Providers`, `App\Jobs`, `App\Listeners` | Delivery and wiring: HTTP, console, queues, providers. Translates input into Payloads, calls one Action, formats output. |
| Business | `App\Actions`, `App\Models`, `App\QueryBuilders`, `App\ValueObjects`, `App\Payloads`, `App\Events`, `App\Enums`, `App\Exceptions`, `App\Policies` | Business rules and data. Knows nothing about HTTP, sessions or the console. |
| Support | `App\Support` | Infrastructure with no business meaning: utils and helpers. |

Dependencies point one way only: `Delivery → Business → Support`.
- The business layer must never import `App\Http`, `App\Console`, `Illuminate\Http`, `Illuminate\Routing`, `Illuminate\Console`, or use the `Request`/`Auth` facades.
- `App\Support` must never import other `App\` layers.
- Models and query builders never use Actions.
- Controllers, commands, listeners and jobs contain no business rules. They call exactly one Action.

These rules are enforced by `tests/Arch`. When a rule fails, fix the code; do not loosen the test.

### 3. Flow of an Operation

Request → FormRequest validates and authorizes → builds a Payload → Controller calls an Action → Action returns a Model/ValueObject → Resource formats the response.

- **Action**: one business operation, `final readonly`, single public `execute()` method. Throws exceptions. This is the context's only public write API. It owns the transaction (nested Actions join it) and fires Events. Never uses the `Request` object.
- **Payload**: `final readonly` typed DTO. Built by the Request (`payload()`). Carries `actorId` when the operation needs the acting user.
- **Model**: data access, relationships, casts, and guards for its own invariants. No business operations, persistence orchestration, transactions or events. Query scopes live in the model's `<Model>QueryBuilder` (`App\QueryBuilders\<Context>`, `final`, extends Eloquent's `Builder`), attached with `#[UseEloquentBuilder]`.
- **Exceptions**: thrown by Actions and Models. Each extends `App\Exceptions\MidasApiException`, lives in `app/Exceptions/<Context>/`, is `final`, and passes its own HTTP status code to the base constructor. The base class renders itself as JSON through `ApiErrorResponse`; there is no mapper or registry.
- **Reads**: may use a model's query builder directly from a controller. **Writes** must go through an Action.
- **Events**: dispatched by Actions. Listeners that do I/O (mail, queue, HTTP) live in `App\Listeners\<Context>`.
- **Dispatching events**: use the `event(new UserRegistered(...))` helper, never the static `UserRegistered::dispatch(...)`. The explicit `new` gives the IDE and PHPStan the constructor's typed signature. Events extend `App\Events\Event` (dispatched after commit) and do not use the `Dispatchable` trait.

### 4. Cross-Context Rules

- A context may call another context's Actions and use its Payloads, Enums and ValueObjects. It must not touch another context's Models directly, except to hold an ID. No Eloquent relations across contexts.
- Cross-context side effects go through Events, not direct calls.
- When a context needs a capability owned by another context, it declares a contract, an adapter implements it, and a service provider binds it. Neither context imports the other. Create `app/Contracts/` and `app/Adapters/` when the first real case appears.
- A class used by only one context stays in that context, even if it looks generic. Move it to `App\Support` only when a second consumer exists and it has no business meaning.
- `Ticketing` is legacy and scheduled for deletion. Do not build on it.

### 5. Naming

- Actions: verb + noun + `Action`: `RegisterUserAction`. Payloads: `<ActionName minus Action>Payload`.
- Controllers: invokable, one per operation: `RegisterController`, extending `ApiController`. Requests: `<Operation>Request`.
- Exceptions: named for the situation, not the type: `InvalidCredentials`, `CannotModifyOwnAccount`.
- Query builders: `<Model>QueryBuilder`. Policies: `<Model>Policy`. Providers: `<Context>ServiceProvider`.
- Tests mirror the source path and are named after the class under test: `app/Actions/Identity/RegisterUserAction.php` → `tests/Feature/Actions/Identity/RegisterUserActionTest.php`.

### 6. Other Locations

- Routes: `routes/api/v1/<context>.php`. The `v1` folder maps to the `v1` prefix and `v1.` name prefix (the app is loaded with an empty `api` prefix). Route files declare their own `auth:sanctum` / `Authorize` middleware. `routes/console.php` stays in `routes/`.
- Versioning: HTTP classes are versioned by folder (`Controllers/V1`, `Requests/V1`, `Resources/V1`). A new version only gets classes for endpoints whose contract changes; every version calls the same Actions.
- Migrations and factories: `database/` (shared). Factories are named `<Model>Factory` and registered on the model.
- Authentication: Sanctum cookie-based SPA flow. Session and auth mechanics (login, logout) live in controllers; Actions never touch the session.
- Authorization: capabilities are `Permission` enum cases mapped to roles in `Role::permissions()`. Each context's `<Context>ServiceProvider` registers them as Gate abilities. Pass the enum itself, never `->value`: `Authorize::using(Permission::ManageUsers)`, `Gate::authorize(Permission::ManageUsers)`. Record-level rules go in `app/Policies/<Context>` (auto-discovered through the `Models` → `Policies` namespace convention). Invariants stay in Actions and Models as exceptions. Actions receive the acting user as an argument or as `actorId` in the Payload and never read `Auth`.
- Architecture tests: `tests/Arch` uses Pest presets (`php()`, `security()`, `strict()`) plus layer and shape rules. `strict()` ignores `ApiController`, `Event` and `App\Models`; models get their own final and strict types rules. Add to `ignoring()` only for a genuine base class or framework convention, with a comment.
- Tests: mirror `app/`; `tests/Arch` holds layer rules. Test every Action directly (`tests/Feature/Actions/<Context>/`). Test controllers through HTTP (`tests/Feature/Http/V1/<Context>/`), which also covers their Requests, Resources, policies and routes. Pure logic (ValueObjects, Enums, Policies with a fake contract) goes in `tests/Unit/`. Give Requests, Payloads, Resources and Models their own test only when they hold real logic.