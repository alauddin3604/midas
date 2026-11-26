<?php

namespace Interface\Identity\Api\Exceptions;

use Application\Identity\Exceptions\CannotModifyOwnAccount;
use Application\Identity\Exceptions\InvalidCredentials;
use Interface\Shared\Api\Exceptions\ApiExceptionMapper;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class IdentityExceptionMapper extends ApiExceptionMapper
{
    /**
     * @return array<class-string<Throwable>, int>
     */
    protected function statusMap(): array
    {
        return [
            CannotModifyOwnAccount::class => Response::HTTP_CONFLICT,
            InvalidCredentials::class => Response::HTTP_UNAUTHORIZED,
        ];
    }
}
