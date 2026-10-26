<?php

declare(strict_types=1);

use Database\Factories\UserFactory;
use Domain\Identity\Actions\UpdateUserAction;
use Domain\Identity\Enums\Role;
use Domain\Identity\Events\UserUpdated;
use Domain\Identity\Exceptions\CannotModifyOwnAccount;
use Domain\Identity\Exceptions\EmailAlreadyRegistered;
use Domain\Identity\Payloads\UpdateUserPayload;
use Illuminate\Support\Facades\Event;

it('updates the user and dispatches the changed attributes', function (): void {
    Event::fake([UserUpdated::class]);
    $actor = UserFactory::new()->admin()->create();
    $user = UserFactory::new()->create(['name' => 'Old Name', 'email' => 'old@example.com']);

    app(UpdateUserAction::class)->execute(
        $actor,
        $user,
        new UpdateUserPayload('New Name', 'old@example.com', Role::Admin),
    );

    expect($user->refresh())
        ->name->toBe('New Name')
        ->role->toBe(Role::Admin);

    Event::assertDispatched(
        UserUpdated::class,
        fn (UserUpdated $event): bool => $event->userId === $user->id
            && $event->actorId === $actor->id
            && $event->changes === [
                'name' => ['old' => 'Old Name', 'new' => 'New Name'],
                'role' => ['old' => 'member', 'new' => 'admin'],
            ],
    );
});

it('does not dispatch an event when nothing changed', function (): void {
    Event::fake([UserUpdated::class]);
    $actor = UserFactory::new()->admin()->create();
    $user = UserFactory::new()->create();

    app(UpdateUserAction::class)->execute(
        $actor,
        $user,
        new UpdateUserPayload($user->name, $user->email, $user->role),
    );

    Event::assertNotDispatched(UserUpdated::class);
});

it('refuses to change its own role', function (): void {
    Event::fake([UserUpdated::class]);
    $actor = UserFactory::new()->admin()->create();

    try {
        app(UpdateUserAction::class)->execute(
            $actor,
            $actor,
            new UpdateUserPayload($actor->name, $actor->email, Role::Member),
        );
    } finally {
        Event::assertNotDispatched(UserUpdated::class);
    }
})->throws(CannotModifyOwnAccount::class);

it('refuses an email that belongs to another user', function (): void {
    Event::fake([UserUpdated::class]);
    $actor = UserFactory::new()->admin()->create();
    $user = UserFactory::new()->create();
    $other = UserFactory::new()->create();

    try {
        app(UpdateUserAction::class)->execute(
            $actor,
            $user,
            new UpdateUserPayload($user->name, $other->email, $user->role),
        );
    } finally {
        Event::assertNotDispatched(UserUpdated::class);
    }
})->throws(EmailAlreadyRegistered::class);
