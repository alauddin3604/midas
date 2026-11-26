<?php

declare(strict_types=1);

namespace Domain\Identity\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;

final readonly class UserAuthenticated implements ShouldDispatchAfterCommit
{
    /**
     * Create a new event instance.
     */
    public function __construct(
        public string $userId,
    ) {}
}
