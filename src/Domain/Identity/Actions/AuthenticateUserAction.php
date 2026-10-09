<?php

declare(strict_types=1);

namespace Domain\Identity\Actions;

use Domain\Identity\Exceptions\InvalidCredentials;
use Domain\Identity\Models\User;
use Domain\Identity\Payloads\AuthenticateUserPayload;
use Illuminate\Contracts\Hashing\Hasher;

final readonly class AuthenticateUserAction
{
    public function __construct(private Hasher $hasher) {}

    /**
     * @throws InvalidCredentials
     */
    public function execute(AuthenticateUserPayload $payload): User
    {
        $user = User::query()->where('email', $payload->email)->first();

        if ($user === null || ! $this->hasher->check($payload->password, $user->password)) {
            throw InvalidCredentials::provided();
        }

        return $user;
    }
}
