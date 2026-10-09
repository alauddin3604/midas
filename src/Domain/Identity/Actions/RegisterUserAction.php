<?php

declare(strict_types=1);

namespace Domain\Identity\Actions;

use Domain\Identity\Events\UserRegistered;
use Domain\Identity\Exceptions\EmailAlreadyRegistered;
use Domain\Identity\Models\User;
use Domain\Identity\Payloads\RegisterUserPayload;

final readonly class RegisterUserAction
{
    /**
     * @throws EmailAlreadyRegistered
     */
    public function execute(RegisterUserPayload $payload): User
    {
        if (User::query()->where('email', $payload->email)->exists()) {
            throw EmailAlreadyRegistered::withEmail($payload->email);
        }

        $user = User::query()->forceCreate($payload->toArray());

        event(new UserRegistered($user->id, $user->email, $user->role->value));

        return $user;
    }
}
