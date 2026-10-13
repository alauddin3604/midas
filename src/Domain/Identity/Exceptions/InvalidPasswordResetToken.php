<?php

declare(strict_types=1);

namespace Domain\Identity\Exceptions;

use Support\Bases\Exceptions\UnprocessableException;

final class InvalidPasswordResetToken extends UnprocessableException
{
    public static function provided(): self
    {
        return new self('This password reset link is invalid or has expired.');
    }
}
