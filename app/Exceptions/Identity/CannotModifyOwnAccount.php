<?php

declare(strict_types=1);

namespace App\Exceptions\Identity;

use App\Exceptions\MidasApiException;
use Symfony\Component\HttpFoundation\Response;

final class CannotModifyOwnAccount extends MidasApiException
{
    public static function roleChange(): self
    {
        return new self('You cannot change your own role.', Response::HTTP_CONFLICT);
    }

    public static function deletion(): self
    {
        return new self('You cannot delete your own account.', Response::HTTP_CONFLICT);
    }
}
