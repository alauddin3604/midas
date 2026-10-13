<?php

declare(strict_types=1);

namespace Domain\Identity\Exceptions;

use Support\Bases\Exceptions\NotFoundException;

final class UserNotFound extends NotFoundException
{
    public static function withEmail(string $email): self
    {
        return new self("User with email [{$email}] does not exist.");
    }
}
