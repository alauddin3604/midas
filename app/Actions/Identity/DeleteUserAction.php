<?php

declare(strict_types=1);

namespace App\Actions\Identity;

use App\Exceptions\Identity\CannotModifyOwnAccount;
use Domain\Identity\Events\UserDeleted;
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
