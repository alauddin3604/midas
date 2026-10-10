# Adding a feature

A recipe for a new business operation. The example is "archive a project" in a new `Projects`
domain; for an existing domain, skip the steps that create folders.

## 1. Domain: the business operation

Create the pieces under `src/Domain/Projects/`.

```php
// Payloads/ArchiveProjectPayload.php
final readonly class ArchiveProjectPayload
{
    public function __construct(public string $reason) {}
}
```

```php
// Exceptions/ProjectAlreadyArchived.php
final class ProjectAlreadyArchived extends DomainException
{
    public static function withId(string $id): self
    {
        return new self("Project [{$id}] is already archived.");
    }
}
```

```php
// Actions/ArchiveProjectAction.php
final readonly class ArchiveProjectAction
{
    /**
     * @throws ProjectAlreadyArchived
     */
    public function execute(User $actor, Project $project, ArchiveProjectPayload $payload): Project
    {
        if ($project->archived_at !== null) {
            throw ProjectAlreadyArchived::withId($project->id);
        }

        $project->forceFill(['archived_at' => now()])->save();

        event(new ProjectArchived($project->id, $actor->id));

        return $project;
    }
}
```

Checklist:

- Action is `final readonly`, suffixed `Action`, has a single `execute()`.
- Payload is `final readonly`, suffixed `Payload`; use named arguments when constructing it.
- Events extend `Support\Bases\Events\DomainEvent` and are fired with `event(new ...)`.
- Scopes go in a `ProjectQueryBuilder`, not on the model.
- Use `now()`, never `new DateTime()`.

## 2. App: delivery

Create the pieces under `src/App/Projects/`.

- **Request** (`Http/Api/V1/Requests/ArchiveRequest.php`): validate input and expose `payload()`.
- **Controller** (`Http/Api/V1/Controllers/ArchiveController.php`): invokable, `final`, extends
  `Support\Bases\Controllers\Controller`; calls the Action and returns a Resource.
- **Livewire page** (alternative, `Http/Dashboard/Livewire/*Page.php`): `final`, extends
  `Livewire\Component`; builds the Payload and calls the Action.
- **Routes**: add a file in `Http/Api/V1/Routes/projects.php` or
  `Http/Dashboard/Routes/projects.php`. It is discovered automatically; declare your own
  middleware.

## 3. Wire the domain (new domain only)

1. Add `src/App/Projects/Providers/ProjectsServiceProvider.php` and register it in
   `bootstrap/providers.php`.
2. Map domain exceptions to statuses with an `ProjectsExceptionMap` implementing
   `Support\Contracts\MapsDomainExceptions`, tagged in `register()`:

   ```php
   $this->app->tag([ProjectsExceptionMap::class], MapsDomainExceptions::TAG);
   ```

3. For new capabilities, add `Permission` cases, grant them in `Role::permissions()`, and protect
   routes with `Authorize::using(Permission::X)`.

## 4. React to it elsewhere (optional)

If another domain must react, add a listener in `App\<Domain>\Listeners` that translates your event
into that domain's Payload and Action, then subscribe it in that domain's provider. Never call the
other domain's models directly. See `RecordIdentityActivity` for the pattern.

## 5. Test and verify

```bash
php artisan test --compact tests/Domain/Projects
php artisan test --compact tests/Arch
composer test    # lint, static analysis, full suite
```

- `tests/Domain/Projects/Actions/ArchiveProjectActionTest.php`: test the Action directly, including
  the failure path.
- `tests/App/Projects/...`: test controllers or Livewire pages through HTTP/Livewire.
- Run `vendor/bin/pint --dirty` before committing.
