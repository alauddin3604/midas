<?php

declare(strict_types=1);

namespace App\Actions\Identity;

use App\Events\Identity\UserDeleted;
use Domain\Identity\Models\User;

final readonly class PerformUserDeletion
{
    public function execute(User $user, ?string $actorId = null): void
    {
        $user->tokens()->delete();
        $user->delete();

        event(new UserDeleted($user->id, $user->email, $actorId));
    }
}
