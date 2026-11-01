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
use DomainException;

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

## 2. Interfaces: delivery

Create the pieces under `src/Interfaces/Projects/`.

- **Request** (`Http/V1/Requests/ArchiveRequest.php`): validate input and expose `payload()`.
- **Controller** (`Http/V1/Controllers/ArchiveController.php`): invokable, `final`, extends
  `App\Http\V1\Controllers\ApiControlelr`; calls the Action and returns a Resource.
- **Routes**: add a file in `routes/v1/projects.php`. It is discovered automatically; declare your own
  middleware.

## 3. Wire the domain (new domain and when needed only)

1. Add `app/Providers/ProjectsServiceProvider.php` and register it in
   `bootstrap/providers.php`.
2. For new capabilities, add `Permission` cases, grant them in `Role::permissions()`, and protect
   routes with `Authorize::using(Permission::X)`.

## 4. React to it elsewhere (optional)

If another domain must react, add a listener in `App\Listeners\<Domain>` that translates your event
into that domain's Payload and Action, then subscribe it in that domain's provider. Never call the
other domain's models directly. See `RecordIdentityActivity` for the pattern.

## 5. Test and verify

```bash
php artisan test --compact tests/Feature/Domain/Projects
php artisan test --compact tests/Unit/Domain/Projects
php artisan test --compact tests/Architecture
composer test    # lint, static analysis, full suite
```

- `tests/Feature/Domain/Projects/Actions/ArchiveProjectActionTest.php`: test the Action directly, including
  the failure path.
- `tests/Feature/App/...`: test controllers or Livewire pages through HTTP/Livewire.
- Run `vendor/bin/pint --dirty` before committing.
