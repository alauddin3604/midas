<?php

declare(strict_types=1);

namespace Domain\Identity\Exceptions;

use DomainException;

final class CannotModifyOwnAccount extends DomainException
{
    public static function roleChange(): self
    {
        return new self('You cannot change your own role.');
    }

    public static function deletion(): self
    {
        return new self('You cannot delete your own account.');
    }
}
