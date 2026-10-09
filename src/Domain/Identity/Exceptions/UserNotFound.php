<?php

declare(strict_types=1);

namespace Domain\Identity\Exceptions;

use Support\Bases\Exceptions\DomainException;

final class UserNotFound extends DomainException
{
    public static function withEmail(string $email): self
    {
        return new self("User with email [{$email}] does not exist.");
    }
}
