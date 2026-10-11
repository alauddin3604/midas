<?php

declare(strict_types=1);

namespace Domain\Identity\Actions;

use Domain\Identity\Events\UserDeleted;
use Domain\Identity\Exceptions\CannotModifyOwnAccount;
use Domain\Identity\Models\User;

final readonly class DeleteUserAction
{
    /**
     * @throws CannotModifyOwnAccount
     */
    public function execute(User $actor, User $user): void
    {
        if ($actor->is($user)) {
            throw CannotModifyOwnAccount::deletion();
        }

        $user->tokens()->delete();
        $user->delete();

        event(new UserDeleted($user->id, $user->email, $actor->id));
    }
}
