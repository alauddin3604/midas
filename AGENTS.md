<laravel-boost-guidelines>
=== .ai/best-practices rules ===

## Code Best Practices

### Method docs

Give every method a one-line summary of what it does. Add more only when the behaviour isn't obvious.

```diff
- public function subscribe(): array
+ /**
+  * Register the listeners for the subscriber.
+  *
+  * @return array<string, string>
+  */
+ public function subscribe(): array
```

### Long method argument list

For more readability, split when the call is longer than 120 characters or has more than three arguments, and always end with a trailing comma.

```diff
- $this->record('identity.password.reset', self::USER, $event->userId, $event->userId);
+ $this->record(
+     'identity.password.reset',
+     self::USER,
+     $event->userId,
+     $event->userId,
+ );
```

### Model Relationships

Add generic return type to relations in child of Illuminate\Database\Eloquent\Model

```diff
 use Domain\Identity\Models\Address;
 use Illuminate\Database\Eloquent\Model;
 use Illuminate\Database\Eloquent\Relations\HasMany;

 class User extends Model
 {
+    /** @return HasMany<Address, $this> */
     public function addresses(): HasMany
     {
         return $this->hasMany(Address::class);
     }
 }
```

### Constants

All constants must have visibility modifier, typed and using CONSTANT_CASE.

```diff
- const MaxSize = 1024;
+ private const int MAX_SIZE = 1024;
```

### Enums

All enums must use PascalCase and backed.

```diff
- enum Role
- {
-     case ADMIN;
- }
+ enum Role: string
+ {
+     case Admin = 'admin';
+     case Member = 'member';
+ }
```

### Named arguments

Use named arguments when constructing Payloads or calling a method with optional, boolean or several same-typed arguments. Positional arguments are fine for one or two obvious values.

```diff
- new RecordAuditEntryPayload('identity.user.updated', 'user', $id, now(), $actorId, $changes);
+ new RecordAuditEntryPayload(
+     event: 'identity.user.updated',
+     subjectType: 'user',
+     subjectId: $id,
+     occurredAt: now(),
+     actorId: $actorId,
+     changes: $changes,
+ );
```

### Eloquent attributes over properties

Configure models with attributes instead of class properties: `#[Table]`, `#[Fillable]`, `#[Hidden]`, `#[UseFactory]` and `#[UseEloquentBuilder]`.

```diff
- protected $table = 'users';
- protected $fillable = ['name', 'email', 'password'];
- protected $hidden = ['password', 'remember_token'];
+ #[Table('users')]
+ #[Fillable(['name', 'email', 'password'])]
+ #[Hidden(['password', 'remember_token'])]
  class User extends Authenticatable
```

### Time

Use the `now()` helper for the current time, never `new DateTime()` or `Carbon::now()`, so tests can freeze it with `travelTo()`.

```diff
- 'occurred_at' => new DateTimeImmutable,
+ 'occurred_at' => now(),
```

### Custom query builders

Do not write scope methods (`scopeX()` or `#[Scope]`) on a model. Give the model its own query builder class named `<Model>QueryBuilder`, extending `Illuminate\Database\Eloquent\Builder`, place it in `src/Domain/<Domain>/QueryBuilders/` and put every scope there as a plain public method. Attach it to the model with `#[UseEloquentBuilder]`.

```diff
+ /**
+  * @extends Builder<AuditEntry>
+  */
+ final class AuditEntryQueryBuilder extends Builder
+ {
+     /**
+      * Match entries of the given event name.
+      */
+     public function ofEvent(?string $event): static
+     {
+         return $this->when($event, fn (Builder $query, string $event) => $query->where('event', $event));
+     }
+ }

+ #[UseEloquentBuilder(AuditEntryQueryBuilder::class)]
  class AuditEntry extends Model
  {
-     #[Scope]
-     protected function ofEvent(Builder $query, ?string $event): void
-     {
-         ...
-     }
  }
```

=== .ai/code-architecture rules ===

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

- Routes: `src/App/<Domain>/Http/Api/V1/Routes/<domain>.php` and `src/App/<Domain>/Http/Dashboard/Routes/<domain>.php`, auto-discovered by `App\Routing\DomainRouteLoader`. Version folder `V1` maps to the `api/v1` prefix and `api.v1.` name prefix; dashboard routes have no prefix. Route files declare their own `auth`/`guest`/`Authorize` middleware. `routes/console.php` stays in `routes/`.
- `App\Midas` is reserved for the cross-domain app shell (dashboard, `/` redirect). It has no `Domain\Midas` until real business rules need one.
- Migrations and factories: `database/` (shared). Factories are named `<Model>Factory` and registered on the model.
- Blade views: `resources/views/`. Livewire page components: `Dashboard/Livewire/`.
- Authorization: capabilities are `Permission` enum cases mapped to roles in `Role::permissions()`. Each domain's `App\<Domain>\Providers\<Domain>ServiceProvider` registers them as Gate abilities (and its exception map). Pass the enum itself, never `->value`: `Authorize::using(Permission::ManageUsers)`, `$this->authorize(Permission::ManageUsers)`, `@can(Permission::ManageUsers)`. Record-level rules go in `Domain/<Domain>/Policies` (auto-discovered). Invariants stay in Actions as domain exceptions. Actions receive the acting user as an argument and never read `Auth`.
- Tests: `tests/App`, `tests/Domain` and `tests/Support` mirror `src/`; `tests/Arch` holds layer rules. Test every Action directly. Test controllers and Livewire pages through HTTP/Livewire, which also covers their Requests, Resources and routes; give Requests, Payloads, Resources and Models their own test only when they hold real logic.

=== tests rules ===

# Test Enforcement

- Add or update tests for behavior and logic changes when a test provides meaningful regression coverage.
- Pure copy, styling, and layout-only changes do not require new or updated tests.
- When test coverage applies, run the affected tests and ensure they pass.
- Test the changed behavior and its important failure modes, but do not add tests beyond them.
- Read the `testing-best-practices` skill before writing tests.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

=== pest/core rules ===

# Pest

- This project uses Pest. Create tests with `php artisan make:test --pest {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.
- Do not delete tests or test files without approval. They are part of the application.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/pest` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.
- After the feature tests pass, ask the user to run the complete suite with `php artisan test --compact`.

</laravel-boost-guidelines>
