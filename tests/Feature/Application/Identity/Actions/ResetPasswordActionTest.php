<?php

declare(strict_types=1);

use Application\Identity\Actions\ResetPasswordAction;
use Application\Identity\Exceptions\InvalidPasswordResetToken;
use Application\Identity\Payloads\ResetPasswordPayload;
use Database\Factories\UserFactory;
use Domain\Identity\Events\PasswordReset;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

it('resets the password and dispatches an event', function (): void {
    Event::fake([PasswordReset::class]);
    $user = UserFactory::new()->create();
    $token = Password::createToken($user);

    App::make(ResetPasswordAction::class)->execute(
        new ResetPasswordPayload($user->email, $token, 'new-secret-password'),
    );

    expect(Hash::check('new-secret-password', $user->refresh()->password))->toBeTrue();
    Event::assertDispatched(PasswordReset::class, fn (PasswordReset $event): bool => $event->userId === $user->id);
});

it('refuses an invalid token without dispatching an event', function (): void {
    Event::fake([PasswordReset::class]);
    $user = UserFactory::new()->create();

    try {
        App::make(ResetPasswordAction::class)->execute(
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

    App::make(ResetPasswordAction::class)->execute(
        new ResetPasswordPayload($user->email, Password::createToken($user), 'new-secret-password'),
    );

    expect($user->tokens()->count())->toBe(0);
});
