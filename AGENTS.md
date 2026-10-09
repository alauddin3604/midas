<laravel-boost-guidelines>
=== .ai/code-architecture rules ===

## Application Structure & Architecture

### 1. Where Things Live

Domain code lives in `src/Domain/<DomainName>/` — one folder per business capability. HTTP plumbing (controllers, requests, responses) stays in `src/App/<DomainName>/Http/`, but everything that describes the business model — actions, payloads, models, enums — sits inside its domain.

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
- **Model**: data access, relationships, casts, query scopes. No business operations.
- **Exceptions**: thrown by Actions, mapped to HTTP/UI responses in the App layer, never inside the Domain.
- **Reads**: may use model query scopes directly from the App layer. **Writes** must go through an Action.
- **Events**: dispatched by Actions. Listeners that do I/O (mail, queue, HTTP) live in `App\<Domain>\Listeners`.

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
- `App\Prism` is reserved for the cross-domain app shell (dashboard, `/` redirect). It has no `Domain\Prism` until real business rules need one.
- Migrations and factories: `database/` (shared). Factories are named `<Model>Factory` and registered on the model.
- Blade views: `resources/views/`. Livewire page components: `Dashboard/Livewire/`.
- Authorization: capabilities are `Permission` enum cases mapped to roles in `Role::permissions()`. Each domain's `App\<Domain>\Providers\<Domain>ServiceProvider` registers them as Gate abilities (and its exception map). Pass the enum itself, never `->value`: `Authorize::using(Permission::ManageUsers)`, `$this->authorize(Permission::ManageUsers)`, `@can(Permission::ManageUsers)`. Record-level rules go in `Domain/<Domain>/Policies` (auto-discovered). Invariants stay in Actions as domain exceptions. Actions receive the acting user as an argument and never read `Auth`.
- Tests: `tests/App`, `tests/Domain` and `tests/Support` mirror `src/`; `tests/Arch` holds layer rules. Test every Action directly. Test controllers and Livewire pages through HTTP/Livewire, which also covers their Requests, Resources and routes; give Requests, Payloads, Resources and Models their own test only when they hold real logic.

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

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Use `search-docs` before changes that depend on Laravel ecosystem APIs, behavior, configuration, or version-specific syntax. Skip it for copy-only edits and other changes where package documentation is irrelevant. Reuse sufficient results already in context instead of searching again.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists, including path-scoped framework guidelines under `.ai/rules/boost`. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

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

=== livewire/core rules ===

# Livewire

- Livewire allows you to build dynamic, reactive interfaces in PHP without writing JavaScript.
- You can use Alpine.js for client-side interactions instead of JavaScript frameworks.
- Keep state server-side so the UI reflects it. Validate and authorize in actions as you would in HTTP requests.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

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
