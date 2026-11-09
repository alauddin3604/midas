<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

use App\Exceptions\Domain\Contracts\DomainExceptionsMapper;
use Domain\Identity\Exceptions\EmailAlreadyRegistered;
use DomainException;
use Symfony\Component\HttpFoundation\Response;

final class IdentityExceptionsMapper implements DomainExceptionsMapper
{
    /**
     * @return array<class-string<DomainException>, int>
     */
    public function map(): array
    {
        return [
            EmailAlreadyRegistered::class => Response::HTTP_CONFLICT,
        ];
    }
}
