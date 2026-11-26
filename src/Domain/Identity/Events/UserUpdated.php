<?php

declare(strict_types=1);

namespace Domain\Identity\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;

final readonly class UserUpdated implements ShouldDispatchAfterCommit
{
    /**
     * @param  array<string, array{old: string, new: string}>  $changes
     */
    public function __construct(
        public string $userId,
        public string $actorId,
        public array $changes,
    ) {}
}
