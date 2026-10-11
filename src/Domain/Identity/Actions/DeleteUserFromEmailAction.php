<?php

declare(strict_types=1);

namespace Domain\Identity\Actions;

use Domain\Identity\Events\UserDeleted;
use Domain\Identity\Exceptions\UserNotFound;
use Domain\Identity\Models\User;

final readonly class DeleteUserFromEmailAction
{
    /**
     * @throws UserNotFound
     */
    public function execute(string $email): void
    {
        $user = User::query()->where('email', $email)->first();

        if ($user === null) {
            throw UserNotFound::withEmail($email);
        }

        $user->tokens()->delete();
        $user->delete();

        event(new UserDeleted($user->id, $user->email, null));
    }
}
