<?php

declare(strict_types=1);

namespace App\Events\Identity;

use App\Events\Event;

final readonly class UserAuthenticationFailed extends Event
{
    public function __construct(
        public string $email,
    ) {}
}
