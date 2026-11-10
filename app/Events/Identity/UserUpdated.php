<?php

declare(strict_types=1);

namespace App\Events\Identity;

use Support\Bases\Events\DomainEvent;

final readonly class UserUpdated extends DomainEvent
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
