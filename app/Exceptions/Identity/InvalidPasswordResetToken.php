<?php

declare(strict_types=1);

namespace App\Exceptions\Identity;

use App\Exceptions\MidasApiException;

final class InvalidPasswordResetToken extends MidasApiException
{
    public static function provided(): self
    {
        return new self('This password reset link is invalid or has expired.');
    }
}
