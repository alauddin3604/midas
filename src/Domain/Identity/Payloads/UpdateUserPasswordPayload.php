<?php

declare(strict_types=1);

namespace Domain\Identity\Payloads;

use Domain\Identity\ValueObjects\Password;

final readonly class UpdateUserPasswordPayload
{
    public function __construct(public Password $password) {}
}
