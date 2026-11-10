<?php

declare(strict_types=1);

namespace App\Exceptions\Identity;

use App\Exceptions\MidasApiException;
use Symfony\Component\HttpFoundation\Response;

final class EmailAlreadyRegistered extends MidasApiException
{
    public static function withEmail(string $email): self
    {
        return new self(
            "A user with email [{$email}] is already registered.",
            Response::HTTP_CONFLICT,
        );
    }
}
