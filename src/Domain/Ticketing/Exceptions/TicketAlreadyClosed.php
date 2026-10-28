<?php

declare(strict_types=1);

namespace Domain\Ticketing\Exceptions;

use DomainException;

final class TicketAlreadyClosed extends DomainException
{
    public static function create(): self
    {
        return new self('Closed tickets cannot be modified.');
    }
}
