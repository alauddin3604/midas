<?php

declare(strict_types=1);

namespace App\Exceptions\Identity;

use App\Exceptions\MidasApiException;

final class InvalidCredentials extends MidasApiException
{
    public static function provided(): self
    {
        return new self('The provided credentials are incorrect.');
    }
}
