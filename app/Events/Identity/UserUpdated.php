<?php

declare(strict_types=1);

namespace App\Events\Identity;

use App\Events\Event;

final readonly class UserUpdated extends Event
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
