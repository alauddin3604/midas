<?php

declare(strict_types=1);

namespace Application\Identity\Actions;

use Application\Identity\Exceptions\InvalidPasswordResetToken;
use Application\Identity\Payloads\ResetPasswordPayload;
use Domain\Identity\Events\PasswordReset;
use Domain\Identity\Models\User;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Str;

final readonly class ResetPasswordAction
{
    public function __construct(
        private PasswordBroker $broker,
        private Dispatcher $dispatcher,
    ) {}

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

                $this->dispatcher->dispatch(new PasswordReset($user->id));
            },
        );

        if ($status !== PasswordBroker::PASSWORD_RESET) {
            throw InvalidPasswordResetToken::provided();
        }
    }
}
