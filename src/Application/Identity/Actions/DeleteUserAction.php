<?php

declare(strict_types=1);

namespace Application\Identity\Actions;

use Application\Identity\Exceptions\CannotModifyOwnAccount;
use Domain\Identity\Models\User;

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
