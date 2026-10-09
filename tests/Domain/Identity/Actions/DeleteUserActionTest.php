<?php

declare(strict_types=1);

use Database\Factories\UserFactory;
use Domain\Identity\Actions\DeleteUserAction;
use Domain\Identity\Events\UserDeleted;
use Domain\Identity\Exceptions\CannotModifyOwnAccount;
use Illuminate\Support\Facades\Event;

use function Pest\Laravel\assertModelMissing;

it('deletes the user and dispatches an event naming the actor', function (): void {
    Event::fake([UserDeleted::class]);
    $actor = UserFactory::new()->admin()->create();
    $user = UserFactory::new()->create();

    app(DeleteUserAction::class)->execute($actor, $user);

    assertModelMissing($user);
    Event::assertDispatched(
        UserDeleted::class,
        fn (UserDeleted $event): bool => $event->userId === $user->id
            && $event->email === $user->email
            && $event->actorId === $actor->id,
    );
});

it('refuses to delete its own account', function (): void {
    Event::fake([UserDeleted::class]);
    $actor = UserFactory::new()->admin()->create();

    try {
        app(DeleteUserAction::class)->execute($actor, $actor);
    } finally {
        Event::assertNotDispatched(UserDeleted::class);
    }
})->throws(CannotModifyOwnAccount::class);
