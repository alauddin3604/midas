<?php

declare(strict_types=1);

namespace Domain\Identity\Exceptions;

use DomainException;

final class EmailAlreadyRegistered extends DomainException
{
    public static function withEmail(string $email): self
    {
        return new self("A user with email [{$email}] is already registered.");
    }
}
