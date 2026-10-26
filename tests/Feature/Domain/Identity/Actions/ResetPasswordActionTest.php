<?php

declare(strict_types=1);

use Database\Factories\UserFactory;
use Domain\Identity\Actions\ResetPasswordAction;
use Domain\Identity\Events\PasswordReset;
use Domain\Identity\Exceptions\InvalidPasswordResetToken;
use Domain\Identity\Payloads\ResetPasswordPayload;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

it('resets the password and dispatches an event', function (): void {
    Event::fake([PasswordReset::class]);
    $user = UserFactory::new()->create();
    $token = Password::createToken($user);

    app(ResetPasswordAction::class)->execute(
        new ResetPasswordPayload($user->email, $token, 'new-secret-password'),
    );

    expect(Hash::check('new-secret-password', $user->refresh()->password))->toBeTrue();
    Event::assertDispatched(PasswordReset::class, fn (PasswordReset $event): bool => $event->userId === $user->id);
});

it('refuses an invalid token without dispatching an event', function (): void {
    Event::fake([PasswordReset::class]);
    $user = UserFactory::new()->create();

    try {
        app(ResetPasswordAction::class)->execute(
            new ResetPasswordPayload($user->email, 'invalid-token', 'new-secret-password'),
        );
    } finally {
        Event::assertNotDispatched(PasswordReset::class);
    }
})->throws(InvalidPasswordResetToken::class);

it('revokes every API token of the user', function (): void {
    $user = UserFactory::new()->create();
    $user->createToken('phone');
    $user->createToken('laptop');

    app(ResetPasswordAction::class)->execute(
        new ResetPasswordPayload($user->email, Password::createToken($user), 'new-secret-password'),
    );

    expect($user->tokens()->count())->toBe(0);
});
