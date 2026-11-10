<?php

declare(strict_types=1);

namespace App\Events\Identity;

use Support\Bases\Events\DomainEvent;

final readonly class PasswordResetLinkRequested extends DomainEvent
{
    public function __construct(
        public string $email,
    ) {}
}
