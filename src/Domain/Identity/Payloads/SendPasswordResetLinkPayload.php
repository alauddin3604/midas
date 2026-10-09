<?php

declare(strict_types=1);

namespace Domain\Identity\Payloads;

final readonly class SendPasswordResetLinkPayload
{
    public function __construct(public string $email) {}
}
