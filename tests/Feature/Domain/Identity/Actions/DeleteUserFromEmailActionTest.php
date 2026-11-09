<?php

declare(strict_types=1);

use App\Actions\Identity\DeleteUserFromEmailAction;
use Database\Factories\UserFactory;
use Domain\Identity\Events\UserDeleted;
use Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Event;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertModelExists;
use function Pest\Laravel\assertModelMissing;

it('can delete a user from email', function (): void {
    $user = UserFactory::new()->create();

    assertModelExists($user);

    $action = app(DeleteUserFromEmailAction::class);
    $action->execute($user->email);

    assertModelMissing($user);
});

it('dispatches a deleted event without an actor', function (): void {
    Event::fake([UserDeleted::class]);
    $user = UserFactory::new()->create();

    app(DeleteUserFromEmailAction::class)->execute($user->email);

    Event::assertDispatched(
        UserDeleted::class,
        fn (UserDeleted $event): bool => $event->userId === $user->id
            && $event->email === $user->email
            && $event->actorId === null,
    );
});

it('cannot delete user from non exist email', function (): void {
    $action = app(DeleteUserFromEmailAction::class);
    $action->execute('joe@example.com');
})->throws(
    ModelNotFoundException::class,
    'No query results for model ['.User::class.'].',
);

it('revokes the API tokens of the deleted user', function (): void {
    $user = UserFactory::new()->create();
    $user->createToken('phone');

    app(DeleteUserFromEmailAction::class)->execute($user->email);

    assertDatabaseCount('personal_access_tokens', 0);
});
