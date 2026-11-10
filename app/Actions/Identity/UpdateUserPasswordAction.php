<?php

declare(strict_types=1);

namespace App\Actions\Identity;

use App\Events\Identity\PasswordUpdated;
use App\Exceptions\Identity\PasswordUpdateFailed;
use App\Models\Identity\User;
use App\Payloads\Identity\UpdateUserPasswordPayload;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\QueryException;
use Illuminate\Events\Dispatcher;

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
     *
     * @throws PasswordUpdateFailed If the password update fails due to a database error.
     */
    public function execute(User $user, UpdateUserPasswordPayload $data): void
    {
        try {
            $this->database->transaction(function () use ($user, $data): void {
                $user->password = $data->password->value;
                $user->password_changed_at = now();
                $user->save();
            });

            $this->dispatcher->dispatch(new PasswordUpdated($user->id));
        } catch (QueryException $e) {
            throw PasswordUpdateFailed::withMessage('Failed to update the password due to a database error.', $e);
        }
    }
}
