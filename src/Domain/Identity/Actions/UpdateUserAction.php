<?php

declare(strict_types=1);

namespace Domain\Identity\Actions;

use Domain\Identity\Exceptions\CannotModifyOwnAccount;
use Domain\Identity\Exceptions\EmailAlreadyRegistered;
use Domain\Identity\Models\User;
use Domain\Identity\Payloads\UpdateUserPayload;

final readonly class UpdateUserAction
{
    /**
     * @throws CannotModifyOwnAccount
     * @throws EmailAlreadyRegistered
     */
    public function execute(User $actor, User $user, UpdateUserPayload $payload): User
    {
        if ($actor->is($user) && $user->role !== $payload->role) {
            throw CannotModifyOwnAccount::roleChange();
        }

        $isEmailTaken = User::query()
            ->where('email', $payload->email)
            ->whereKeyNot($user->getKey())
            ->exists();

        if ($isEmailTaken) {
            throw EmailAlreadyRegistered::withEmail($payload->email);
        }

        $user->forceFill($payload->toArray());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return $user;
    }
}
