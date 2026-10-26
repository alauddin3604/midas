<?php

declare(strict_types=1);

namespace Domain\Identity\Actions;

use Domain\Identity\Events\PasswordUpdated;
use Domain\Identity\Exceptions\PasswordUpdateFailed;
use Domain\Identity\Models\User;
use Domain\Identity\Payloads\UpdateUserPasswordPayload;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

final readonly class UpdateUserPasswordAction
{
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
            DB::transaction(function () use ($user, $data): void {
                $user->updatePassword($data->password->value);
                $user->save();
            });

            event(new PasswordUpdated($user->id));
        } catch (QueryException $e) {
            throw PasswordUpdateFailed::withMessage('Failed to update the password due to a database error.', $e);
        }
    }
}
