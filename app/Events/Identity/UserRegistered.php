<?php

declare(strict_types=1);

namespace App\Events\Identity;

use App\Events\Event;

final readonly class UserRegistered extends Event
{
    public function __construct(
        public string $userId,
        public string $email,
        public string $role,
    ) {}
}
