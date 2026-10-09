<?php

declare(strict_types=1);

use Database\Factories\UserFactory;
use Domain\Identity\Actions\AuthenticateUserAction;
use Domain\Identity\Events\UserAuthenticated;
use Domain\Identity\Events\UserAuthenticationFailed;
use Domain\Identity\Exceptions\InvalidCredentials;
use Domain\Identity\Payloads\AuthenticateUserPayload;
use Illuminate\Support\Facades\Event;

it('returns the user and dispatches an authenticated event', function (): void {
    Event::fake([UserAuthenticated::class, UserAuthenticationFailed::class]);
    $user = UserFactory::new()->create();

    $authenticated = app(AuthenticateUserAction::class)->execute(
        new AuthenticateUserPayload($user->email, 'password'),
    );

    expect($authenticated->is($user))->toBeTrue();
    Event::assertDispatched(UserAuthenticated::class, fn (UserAuthenticated $event): bool => $event->userId === $user->id);
    Event::assertNotDispatched(UserAuthenticationFailed::class);
});

it('dispatches a failed event for a wrong password', function (): void {
    Event::fake([UserAuthenticated::class, UserAuthenticationFailed::class]);
    $user = UserFactory::new()->create();

    try {
        app(AuthenticateUserAction::class)->execute(
            new AuthenticateUserPayload($user->email, 'wrong-password'),
        );
    } finally {
        Event::assertDispatched(UserAuthenticationFailed::class, fn (UserAuthenticationFailed $event): bool => $event->email === $user->email);
        Event::assertNotDispatched(UserAuthenticated::class);
    }
})->throws(InvalidCredentials::class);

it('dispatches a failed event for an unknown email', function (): void {
    Event::fake([UserAuthenticated::class, UserAuthenticationFailed::class]);

    try {
        app(AuthenticateUserAction::class)->execute(
            new AuthenticateUserPayload('nobody@example.com', 'password'),
        );
    } finally {
        Event::assertDispatched(UserAuthenticationFailed::class, fn (UserAuthenticationFailed $event): bool => $event->email === 'nobody@example.com');
    }
})->throws(InvalidCredentials::class);
