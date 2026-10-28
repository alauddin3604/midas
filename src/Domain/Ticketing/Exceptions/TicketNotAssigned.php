<?php

declare(strict_types=1);

namespace Domain\Ticketing\Exceptions;

use Domain\Ticketing\Enums\TicketStatus;
use DomainException;

final class TicketNotAssigned extends DomainException
{
    public static function forStatus(TicketStatus $to): self
    {
        return new self("Ticket must be assigned before moving to {$to->value}.");
    }
}
