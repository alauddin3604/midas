<?php

declare(strict_types=1);

namespace App\Actions\Identity;

use App\Events\Identity\UserRegistered;
use App\Models\Identity\User;
use App\Payloads\Identity\RegisterUserPayload;
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
