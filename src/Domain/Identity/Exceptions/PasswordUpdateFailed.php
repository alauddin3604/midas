<?php

declare(strict_types=1);

namespace Domain\Identity\Exceptions;

use Support\Bases\Exceptions\InternalServerErrorException;

final class PasswordUpdateFailed extends InternalServerErrorException
{
    public function __construct(string $message = 'Failed to update the password.', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
