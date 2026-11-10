<?php

declare(strict_types=1);

namespace App\Payloads\Identity;

final readonly class SendPasswordResetLinkPayload
{
    public function __construct(public string $email) {}
}
