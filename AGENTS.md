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

=== foundation rules ===

# Laravel Boost Guidelines

## Foundational Context

This application is a Laravel application running on PHP 8.4. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If a frontend change doesn't show in the UI or you get a "Unable to locate file in Vite manifest" error, run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

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

</laravel-boost-guidelines>
