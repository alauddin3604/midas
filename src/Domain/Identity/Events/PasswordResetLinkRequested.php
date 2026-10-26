<?php

declare(strict_types=1);

namespace Domain\Identity\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;

final readonly class PasswordResetLinkRequested implements ShouldDispatchAfterCommit
{
    public function __construct(
        public string $email,
    ) {}
}
