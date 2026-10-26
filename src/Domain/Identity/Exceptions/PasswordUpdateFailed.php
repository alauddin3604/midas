<?php

declare(strict_types=1);

namespace Domain\Identity\Exceptions;

use DomainException;
use Throwable;

final class PasswordUpdateFailed extends DomainException
{
    public static function withMessage(string $message, Throwable $previous): self
    {
        return new self($message, 0, $previous);
    }
}
