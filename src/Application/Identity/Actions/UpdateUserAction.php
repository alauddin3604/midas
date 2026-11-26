<?php

declare(strict_types=1);

namespace Application\Identity\Actions;

use Application\Identity\Exceptions\CannotModifyOwnAccount;
use Application\Identity\Payloads\UpdateUserPayload;
use Domain\Identity\Enums\Role;
use Domain\Identity\Events\UserUpdated;
use Domain\Identity\Models\User;
use Illuminate\Contracts\Events\Dispatcher;

final readonly class UpdateUserAction
{
    public function __construct(private Dispatcher $dispatcher) {}

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
            $this->dispatcher->dispatch(new UserUpdated($user->id, $actor->id, $changes));
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
