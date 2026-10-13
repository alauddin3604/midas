<?php

declare(strict_types=1);

namespace Domain\Identity\Exceptions;

use Support\Bases\Exceptions\ConflictException;

final class EmailAlreadyRegistered extends ConflictException
{
    public static function withEmail(string $email): self
    {
        return new self("A user with email [{$email}] is already registered.");
    }
}
