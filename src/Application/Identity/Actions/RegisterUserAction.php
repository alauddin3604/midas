<?php

declare(strict_types=1);

namespace Application\Identity\Actions;

use Application\Identity\Payloads\RegisterUserPayload;
use Domain\Identity\Events\UserRegistered;
use Domain\Identity\Models\User;
use Illuminate\Contracts\Events\Dispatcher;

final readonly class RegisterUserAction
{
    public function __construct(private readonly Dispatcher $dispatcher) {}

    public function execute(RegisterUserPayload $payload): User
    {
        $user = User::query()->forceCreate($payload->toArray());

        $this->dispatcher->dispatch(new UserRegistered($user->id, $user->email, $user->role->value));

        return $user;
    }
}
