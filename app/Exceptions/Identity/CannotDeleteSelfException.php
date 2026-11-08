<?php

declare(strict_types=1);

namespace App\Exceptions\Identity;

use App\Exceptions\MidasApiException;
use Symfony\Component\HttpFoundation\Response;

final class CannotDeleteSelfException extends MidasApiException
{
    public function __construct()
    {
        parent::__construct('Cannot delete own account.', Response::HTTP_CONFLICT);
    }
}
