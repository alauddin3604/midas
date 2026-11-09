<?php

declare(strict_types=1);

namespace App\Payloads\Identity;

use Domain\Identity\ValueObjects\Password;

final readonly class UpdateUserPasswordPayload
{
    public function __construct(public Password $password) {}
}
