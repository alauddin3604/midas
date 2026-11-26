<?php

declare(strict_types=1);

namespace Domain\Identity\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;

final readonly class UserDeleted implements ShouldDispatchAfterCommit
{
    public function __construct(
        public string $userId,
        public string $email,
        public ?string $actorId,
    ) {}
}
