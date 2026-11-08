<?php

declare(strict_types=1);

namespace App\Actions\Identity;

use App\Exceptions\Identity\CannotDeleteSelfException;
use Domain\Identity\Events\UserDeleted;
use Domain\Identity\Models\User;

final readonly class DeleteUser
{
    public function handle(User $actor, User $user): void
    {
        if ($actor->is($user)) {
            throw new CannotDeleteSelfException;
        }

        $user->delete();

        event(new UserDeleted(
            userId: $user->id,
            email: $user->email,
            actorId: $actor->id,
        ));
    }
}
