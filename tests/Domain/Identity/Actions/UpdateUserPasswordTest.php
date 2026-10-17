<?php

declare(strict_types=1);

use Database\Factories\UserFactory;
use Domain\Identity\Actions\UpdateUserPasswordAction;
use Domain\Identity\Events\PasswordUpdated;
use Domain\Identity\Exceptions\PasswordUpdateFailed;
use Domain\Identity\Models\User;
use Domain\Identity\Payloads\UpdateUserPasswordPayload;
use Domain\Identity\ValueObjects\Password;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;

it('can update user password', function (): void {
    $user = UserFactory::new()->create();

    $payload = new UpdateUserPasswordPayload(
        password: Password::fromString('NewPassw0rd'),
    );

    expect($user->password_changed_at)->toBeNull('Password changed timestamp should be null before the password update.');

    app(UpdateUserPasswordAction::class)->execute($user, $payload);

    expect($user->password_changed_at)->not()->toBeNull('Password changed timestamp should not be null after the password update.');
    expect($user->password)->not()->toBe('NewPassw0rd', 'Password should be hashed and not stored in plain text.');
    expect(Hash::check('NewPassw0rd', $user->password))->toBeTrue('The hashed password should match the new password when checked.');
});

it('dispatches PasswordUpdated event after password update', function (): void {
    Event::fake();

    $user = UserFactory::new()->create();

    $payload = new UpdateUserPasswordPayload(
        password: Password::fromString('AnotherPassw0rd'),
    );

    app(UpdateUserPasswordAction::class)->execute($user, $payload);

    Event::assertDispatched(PasswordUpdated::class, [
        'userId' => $user->id,
    ]);
});

it('throws an exception when trying to update password for a non-existent user', function (): void {
    Event::fake();
    $nonExistentUser = new User;
    $nonExistentUser->id = 'non-existent-id';

    $payload = new UpdateUserPasswordPayload(
        password: Password::fromString('SomePassw0rd'),
    );

    app(UpdateUserPasswordAction::class)->execute($nonExistentUser, $payload);

    Event::assertNotDispatched(PasswordUpdated::class, [
        'userId' => $nonExistentUser->id,
    ]);
})->throws(PasswordUpdateFailed::class, 'Failed to update the password due to a database error.');
