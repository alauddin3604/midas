## Application Structure & Architecture

### 1. Where Things Live

Domain code lives in `src/Domain/<DomainName>/` — one folder per business capability. HTTP plumbing (controllers, requests, responses) stays in `src/App/<DomainName>/Http/`, but everything that describes the business model — actions, payloads, models, query builders, enums — sits inside its domain.

```
src/
├── App/
│   └── <Domain>/
│       ├── Console/               ← Console commands go here
│       ├── Http/
│       │   ├── Api/
│       │   │   └── V1/
│       │   │       ├── Controllers/
│       │   │       ├── Requests/
│       │   │       └── Resources/
│       │   └── Dashboard/
│       │       ├── Controllers/
│       │       ├── Livewire/
│       │       └── ViewModels/    ← To place class that prepares all the data a specific view needs
│       ├── Jobs/
│       ├── Listeners/
│       └── Providers/             ← <Domain>ServiceProvider: Gate abilities, exception map
├── Domain/
│   └── <Domain>/
│       ├── Actions/
│       ├── Enums/
│       ├── Events/
│       ├── Exceptions/
│       ├── Models/
│       ├── Payloads/
│       ├── QueryBuilders/         ← <Model>QueryBuilder: every query scope of a model
│       └── ValueObjects/
└── Support/
    ├── Bases/
    │   ├── Controllers/
    │   │   └── Controller.php     ← Abstract class for controllers
    │   └── Jobs/
    │       └── Job.php            ← Abstract class for jobs
    ├── Providers/
    │   └── AppServiceProvider.php
    ├── Services/                  ← The external services
    └── Utils/                     ← Place utils / helpers here
```

### 2. Layers and Dependency Rules

| Layer | Namespace | Responsibility |
|---|---|---|
| Domain | `Domain\<Domain>\` | Business rules and data. Knows nothing about HTTP, Livewire, queues or the console. |
| App | `App\<Domain>\` | Delivery mechanisms: HTTP, Livewire, console, jobs. Translates input into Payloads, calls Actions, formats output. |
| Support | `Support\` | Cross-cutting infrastructure with no business meaning: base classes, providers, external service clients, utils. |

Dependencies point inward only: `App → Domain → Support`.
- `Domain` must never import from `App`, `Livewire`, `Illuminate\Http`, or the `Request`/`Auth` facades.
- `Support` must never import from `Domain` or `App`.
- Controllers, Livewire components, jobs and commands contain no business rules. They call exactly one Action.

### 3. Flow of an Operation

Request/Livewire form → validates → builds a Payload → calls an Action → returns a Model/ValueObject → Resource/View formats the response.

- **Action**: one business operation, `final readonly`, single public `execute()` method. Throws domain Exceptions. This is the domain's only public write API.
- **Payload**: `final readonly` typed DTO. Built by the Request (`payload()`) or by the Livewire component.
- **Model**: data access, relationships, casts. No business operations. Query scopes live in the model's `<Model>QueryBuilder` (`Domain\<Domain>\QueryBuilders`), attached with `#[UseEloquentBuilder]`.
- **Exceptions**: thrown by Actions, mapped to HTTP/UI responses in the App layer, never inside the Domain.
- **Reads**: may use a model's query builder methods directly from the App layer. **Writes** must go through an Action.
- **Events**: dispatched by Actions. Listeners that do I/O (mail, queue, HTTP) live in `App\<Domain>\Listeners`.
- **Dispatching events**: use the `event(new UserRegistered(...))` helper, never the static `UserRegistered::dispatch(...)`. The explicit `new` gives the IDE and PHPStan the constructor's typed signature. Events extend `Support\Bases\Events\DomainEvent` (dispatched after commit) and do not use the `Dispatchable` trait.

### 4. Cross-Domain Rules

- A domain may call another domain's Actions and use its Payloads, Enums and ValueObjects. It must not touch another domain's Models directly, except to hold an ID.
- Cross-domain side effects go through Events, not direct calls.
- A class used by only one domain stays in that domain, even if it looks generic. Move it to `Support` only when a second consumer exists and it has no business meaning.

### 5. Naming

- Actions: verb + noun + `Action`: `RegisterUserAction`. Payloads: `<ActionName minus Action>Payload`.
- Controllers: invokable, one per operation: `RegisterController`. Requests: `<Operation>Request`.
- Livewire: page components `<Name>Page`, others by role.
- Exceptions: named for the situation, not the type: `EmailAlreadyRegistered`.
- Tests mirror the source path and are named after the class under test: `src/Domain/Identity/Actions/RegisterUserAction.php` → `tests/Domain/Identity/Actions/RegisterUserActionTest.php`.

### 6. Other Locations

- Routes: `src/App/<Domain>/Http/Api/V1/Routes/<domain>.php` and `src/App/<Domain>/Http/Dashboard/Routes/<domain>.php`, auto-discovered by `Support\Utils\DomainRouteLoader`. Version folder `V1` maps to the `api/v1` prefix and `api.v1.` name prefix; dashboard routes have no prefix. Route files declare their own `auth`/`guest`/`Authorize` middleware. `routes/console.php` stays in `routes/`.
- `App\Midas` is reserved for the cross-domain app shell (dashboard, `/` redirect). It has no `Domain\Midas` until real business rules need one.
- Migrations and factories: `database/` (shared). Factories are named `<Model>Factory` and registered on the model.
- Blade views: `resources/views/`. Livewire page components: `Dashboard/Livewire/`.
- Authorization: capabilities are `Permission` enum cases mapped to roles in `Role::permissions()`. Each domain's `App\<Domain>\Providers\<Domain>ServiceProvider` registers them as Gate abilities (and its exception map). Pass the enum itself, never `->value`: `Authorize::using(Permission::ManageUsers)`, `$this->authorize(Permission::ManageUsers)`, `@can(Permission::ManageUsers)`. Record-level rules go in `Domain/<Domain>/Policies` (auto-discovered). Invariants stay in Actions as domain exceptions. Actions receive the acting user as an argument and never read `Auth`.
- Tests: `tests/App`, `tests/Domain` and `tests/Support` mirror `src/`; `tests/Arch` holds layer rules. Test every Action directly. Test controllers and Livewire pages through HTTP/Livewire, which also covers their Requests, Resources and routes; give Requests, Payloads, Resources and Models their own test only when they hold real logic.
