<?php

declare(strict_types=1);

namespace App\Actions\Identity;

use App\Events\Identity\UserAuthenticated;
use App\Events\Identity\UserAuthenticationFailed;
use App\Exceptions\Identity\InvalidCredentials;
use App\Models\Identity\User;
use App\Payloads\Identity\AuthenticateUserPayload;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Hashing\Hasher;

final readonly class AuthenticateUserAction
{
    public function __construct(
        private Hasher $hasher,
        private Dispatcher $dispatcher,
    ) {}

    /**
     * @throws InvalidCredentials
     */
    public function execute(AuthenticateUserPayload $payload): User
    {
        $user = User::query()->where('email', $payload->email)->first();

        if ($user === null || ! $this->hasher->check($payload->password, $user->password)) {
            $this->dispatcher->dispatch(new UserAuthenticationFailed($payload->email));

            throw InvalidCredentials::provided();
        }

        $this->dispatcher->dispatch(new UserAuthenticated($user->id));

        return $user;
    }
}
