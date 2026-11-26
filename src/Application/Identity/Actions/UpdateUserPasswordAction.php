<?php

declare(strict_types=1);

namespace Application\Identity\Actions;

use Application\Identity\Payloads\UpdateUserPasswordPayload;
use Domain\Identity\Events\PasswordUpdated;
use Domain\Identity\Models\User;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\DatabaseManager;

final readonly class UpdateUserPasswordAction
{
    public function __construct(
        private DatabaseManager $database,
        private Dispatcher $dispatcher,
    ) {}

    /**
     * Updates the password for a given user.
     *
     * @param  User  $user  The user whose password is to be updated.
     * @param  UpdateUserPasswordPayload  $data  The payload containing the new password.
     */
    public function execute(User $user, UpdateUserPasswordPayload $data): void
    {
        $this->database->transaction(function () use ($user, $data): void {
            $user->password = $data->password->value;
            $user->password_changed_at = now();
            $user->save();

            $this->dispatcher->dispatch(new PasswordUpdated($user->id));
        });
    }
}
