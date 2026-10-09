<?php

declare(strict_types=1);

namespace Domain\Identity\Actions;

use Domain\Identity\Payloads\SendPasswordResetLinkPayload;
use Illuminate\Contracts\Auth\PasswordBroker;

final readonly class SendPasswordResetLinkAction
{
    public function __construct(private PasswordBroker $broker) {}

    /**
     * Never reveals whether the email belongs to an account.
     */
    public function execute(SendPasswordResetLinkPayload $payload): void
    {
        $this->broker->sendResetLink(['email' => $payload->email]);
    }
}
