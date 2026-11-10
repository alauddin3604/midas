<?php

declare(strict_types=1);

namespace App\Actions\Identity;

use App\Events\Identity\UserRegistered;
use App\Exceptions\Identity\EmailAlreadyRegistered;
use App\Payloads\Identity\RegisterUserPayload;
use Domain\Identity\Models\User;

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
