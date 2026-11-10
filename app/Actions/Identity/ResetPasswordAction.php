<?php

declare(strict_types=1);

namespace App\Actions\Identity;

use App\Events\Identity\PasswordReset;
use App\Exceptions\Identity\InvalidPasswordResetToken;
use App\Payloads\Identity\ResetPasswordPayload;
use Domain\Identity\Models\User;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Support\Str;

final readonly class ResetPasswordAction
{
    public function __construct(private PasswordBroker $broker) {}

    /**
     * @throws InvalidPasswordResetToken
     */
    public function execute(ResetPasswordPayload $payload): void
    {
        $status = $this->broker->reset(
            $payload->toArray(),
            function (CanResetPassword $user, string $password): void {
                /** @var User $user */
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                $user->tokens()->delete();

                event(new PasswordReset($user->id));
            },
        );

        if ($status !== PasswordBroker::PASSWORD_RESET) {
            throw InvalidPasswordResetToken::provided();
        }
    }
}
