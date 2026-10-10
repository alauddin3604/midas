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
