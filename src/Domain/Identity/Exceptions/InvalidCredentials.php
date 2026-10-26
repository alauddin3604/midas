<?php

declare(strict_types=1);

namespace Domain\Identity\Exceptions;

use DomainException;

final class InvalidCredentials extends DomainException
{
    public static function provided(): self
    {
        return new self('The provided credentials are incorrect.');
    }
}
