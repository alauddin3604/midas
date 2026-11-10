<?php

declare(strict_types=1);

namespace App\Actions\Identity;

use App\Events\Identity\UserUpdated;
use App\Exceptions\Identity\CannotModifyOwnAccount;
use App\Models\Identity\User;
use App\Payloads\Identity\UpdateUserPayload;
use Domain\Identity\Enums\Role;

final readonly class UpdateUserAction
{
    /**
     * @throws CannotModifyOwnAccount
     */
    public function execute(User $actor, User $user, UpdateUserPayload $payload): User
    {
        if ($actor->is($user) && $user->role !== $payload->role) {
            throw CannotModifyOwnAccount::roleChange();
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
