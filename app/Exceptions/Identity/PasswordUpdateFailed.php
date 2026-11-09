<?php

declare(strict_types=1);

namespace App\Exceptions\Identity;

use App\Exceptions\MidasApiException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class PasswordUpdateFailed extends MidasApiException
{
    public static function withMessage(string $message, Throwable $previous): self
    {
        return new self($message, Response::HTTP_INTERNAL_SERVER_ERROR, 0, $previous);
    }
}
