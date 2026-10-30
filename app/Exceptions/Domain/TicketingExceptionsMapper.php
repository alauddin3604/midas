<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

use App\Exceptions\Domain\Contracts\DomainExceptionsMapper;
use Domain\Ticketing\Exceptions\TicketAlreadyClosed;
use Override;
use Symfony\Component\HttpFoundation\Response;

final class TicketingExceptionsMapper implements DomainExceptionsMapper
{
    #[Override]
    public function map(): array
    {
        return [
            TicketAlreadyClosed::class => Response::HTTP_CONFLICT,
        ];
    }
}
