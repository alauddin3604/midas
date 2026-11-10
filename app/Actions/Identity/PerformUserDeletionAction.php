<?php

declare(strict_types=1);

namespace App\Actions\Identity;

use App\Events\Identity\UserDeleted;
use App\Models\Identity\User;

final readonly class PerformUserDeletionAction
{
    public function execute(User $user, ?string $actorId = null): void
    {
        $user->tokens()->delete();
        $user->delete();

        event(new UserDeleted($user->id, $user->email, $actorId));
    }
}
