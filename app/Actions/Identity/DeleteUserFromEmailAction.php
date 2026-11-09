<?php

declare(strict_types=1);

namespace App\Actions\Identity;

use Domain\Identity\Exceptions\UserNotFound;
use Domain\Identity\Models\User;

final readonly class DeleteUserFromEmailAction
{
    public function __construct(
        private PerformUserDeletion $performUserDeletion,
    ) {}

    /**
     * @throws UserNotFound
     */
    public function execute(string $email): void
    {
        $user = User::query()->where('email', $email)->first();

        if ($user === null) {
            throw UserNotFound::withEmail($email);
        }

        $this->performUserDeletion->execute($user);
    }
}
