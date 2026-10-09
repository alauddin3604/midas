<?php

declare(strict_types=1);

namespace Domain\Identity\Events;

use Support\Bases\Events\DomainEvent;

final readonly class UserAuthenticationFailed extends DomainEvent
{
    public function __construct(
        public string $email,
    ) {}
}
