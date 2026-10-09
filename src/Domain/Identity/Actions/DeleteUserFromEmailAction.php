<?php

declare(strict_types=1);

namespace Domain\Identity\Actions;

use Domain\Identity\Exceptions\UserNotFound;
use Domain\Identity\Models\User;

final readonly class DeleteUserFromEmailAction
{
    /**
     * @throws UserNotFound
     */
    public function execute(string $email): void
    {
        $deleteCount = User::query()
            ->where('email', $email)
            ->delete();

        if ($deleteCount === 0) {
            throw UserNotFound::withEmail($email);
        }
    }
}
