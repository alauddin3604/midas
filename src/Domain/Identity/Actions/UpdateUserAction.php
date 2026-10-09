<?php

declare(strict_types=1);

namespace Domain\Identity\Actions;

use Domain\Identity\Enums\Role;
use Domain\Identity\Events\UserUpdated;
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

        $changes = $this->changes($user);

        $user->save();

        if ($changes !== []) {
            event(new UserUpdated($user->id, $actor->id, $changes));
        }

        return $user;
    }

    /**
     * @return array<string, array{old: string, new: string}>
     */
    private function changes(User $user): array
    {
        $changes = [];

        foreach (['name', 'email', 'role'] as $attribute) {
            if (! $user->isDirty($attribute)) {
                continue;
            }

            $changes[$attribute] = [
                'old' => $this->scalar($user->getOriginal($attribute)),
                'new' => $this->scalar($user->getAttribute($attribute)),
            ];
        }

        return $changes;
    }

    private function scalar(string|Role $value): string
    {
        return $value instanceof Role ? $value->value : $value;
    }
}
