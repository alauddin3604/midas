<?php

use Domain\Identity\Actions\RegisterUserAction;
use Domain\Identity\Enums\Role;
use Domain\Identity\Events\UserRegistered;
use Domain\Identity\Exceptions\EmailAlreadyRegistered;
use Domain\Identity\Models\User;
use Domain\Identity\Payloads\RegisterUserPayload;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\assertDatabaseHas;

it('registers a member with a hashed password', function () {
    $user = app(RegisterUserAction::class)->execute(
        new RegisterUserPayload('Jane Doe', 'jane@example.com', 'secret-password'),
    );

    assertDatabaseHas('users', ['id' => $user->id, 'email' => 'jane@example.com']);

    expect($user->role)->toBe(Role::Member)
        ->and($user->password)->not->toBe('secret-password')
        ->and(Hash::check('secret-password', $user->password))->toBeTrue();
});

it('registers a user with the requested role', function () {
    $user = app(RegisterUserAction::class)->execute(
        new RegisterUserPayload('Jane Doe', 'jane@example.com', 'secret-password', Role::Admin),
    );

    expect($user->refresh()->role)->toBe(Role::Admin);
});

it('dispatches a registered event', function () {
    Event::fake([UserRegistered::class]);

    $user = app(RegisterUserAction::class)->execute(
        new RegisterUserPayload('Jane Doe', 'jane@example.com', 'secret-password', Role::Admin),
    );

    Event::assertDispatched(
        UserRegistered::class,
        fn (UserRegistered $event): bool => $event->userId === $user->id
            && $event->email === 'jane@example.com'
            && $event->role === 'admin',
    );
});

it('refuses to register a duplicate email', function () {
    User::factory()->create(['email' => 'jane@example.com']);

    app(RegisterUserAction::class)->execute(
        new RegisterUserPayload('Jane Doe', 'jane@example.com', 'secret-password'),
    );
})->throws(EmailAlreadyRegistered::class);
