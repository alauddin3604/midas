<?php

declare(strict_types=1);

namespace Domain\Identity\Actions;

use Domain\Identity\Exceptions\InvalidPasswordResetToken;
use Domain\Identity\Models\User;
use Domain\Identity\Payloads\ResetPasswordPayload;
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
            },
        );

        if ($status !== PasswordBroker::PASSWORD_RESET) {
            throw InvalidPasswordResetToken::provided();
        }
    }
}
