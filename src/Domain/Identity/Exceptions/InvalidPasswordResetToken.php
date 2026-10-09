<?php

declare(strict_types=1);

namespace Domain\Identity\Exceptions;

use Support\Bases\Exceptions\DomainException;

final class InvalidPasswordResetToken extends DomainException
{
    public static function provided(): self
    {
        return new self('This password reset link is invalid or has expired.');
    }
}
