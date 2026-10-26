<?php

declare(strict_types=1);

use Database\Factories\UserFactory;
use Domain\Identity\Actions\DeleteUserFromEmailAction;
use Domain\Identity\Events\UserDeleted;
use Domain\Identity\Exceptions\UserNotFound;
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
    UserNotFound::class,
    'User with email [joe@example.com] does not exist.',
);

it('revokes the API tokens of the deleted user', function (): void {
    $user = UserFactory::new()->create();
    $user->createToken('phone');

    app(DeleteUserFromEmailAction::class)->execute($user->email);

    assertDatabaseCount('personal_access_tokens', 0);
});
