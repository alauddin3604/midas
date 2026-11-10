<?php

declare(strict_types=1);

namespace App\Events\Identity;

use App\Events\Event;

final readonly class UserDeleted extends Event
{
    public function __construct(
        public string $userId,
        public string $email,
        public ?string $actorId,
    ) {}
}
