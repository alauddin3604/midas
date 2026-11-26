<?php

use Application\Identity\Actions\RegisterUserAction;
use Application\Identity\Payloads\RegisterUserPayload;
use Domain\Identity\Enums\Role;
use Domain\Identity\Events\UserRegistered;
use Domain\Identity\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\assertDatabaseHas;

it('registers a member with a hashed password', function () {
    $user = App::make(RegisterUserAction::class)->execute(
        new RegisterUserPayload('Jane Doe', 'jane@example.com', 'secret-password'),
    );

    assertDatabaseHas('users', ['id' => $user->id, 'email' => 'jane@example.com']);

    expect($user->role)->toBe(Role::Member)
        ->and($user->password)->not->toBe('secret-password')
        ->and(Hash::check('secret-password', $user->password))->toBeTrue();
});

it('registers a user with the requested role', function () {
    $user = App::make(RegisterUserAction::class)->execute(
        new RegisterUserPayload('Jane Doe', 'jane@example.com', 'secret-password', Role::Admin),
    );

    expect($user->refresh()->role)->toBe(Role::Admin);
});

it('dispatches a registered event', function () {
    Event::fake([UserRegistered::class]);

    $user = App::make(RegisterUserAction::class)->execute(
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

    App::make(RegisterUserAction::class)->execute(
        new RegisterUserPayload('Jane Doe', 'jane@example.com', 'secret-password'),
    );
})->throws(QueryException::class);
