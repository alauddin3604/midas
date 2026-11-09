<?php

declare(strict_types=1);

namespace App\Actions\Identity;

use App\Events\Identity\PasswordUpdated;
use App\Exceptions\Identity\PasswordUpdateFailed;
use App\Payloads\Identity\UpdateUserPasswordPayload;
use Domain\Identity\Models\User;
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
                $user->password = $data->password->value;
                $user->password_changed_at = now();
                $user->save();
            });

            event(new PasswordUpdated($user->id));
        } catch (QueryException $e) {
            throw PasswordUpdateFailed::withMessage('Failed to update the password due to a database error.', $e);
        }
    }
}
