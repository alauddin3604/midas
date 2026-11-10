<?php

declare(strict_types=1);

namespace App\Actions\Identity;

use App\Exceptions\Identity\CannotModifyOwnAccount;
use App\Models\Identity\User;

final readonly class DeleteUserAction
{
    public function __construct(
        private PerformUserDeletionAction $performUserDeletion,
    ) {}

    /**
     * @throws CannotModifyOwnAccount
     */
    public function execute(User $actor, User $user): void
    {
        if ($actor->is($user)) {
            throw CannotModifyOwnAccount::deletion();
        }

        $this->performUserDeletion->execute($user, $actor->id);
    }
}
