<?php

declare(strict_types=1);

namespace Domain\Ticketing\Exceptions;

use Domain\Ticketing\Enums\TicketStatus;
use DomainException;

final class InvalidStatusTransition extends DomainException
{
    public static function between(TicketStatus $from, TicketStatus $to): self
    {
        return new self("Cannot move ticket from {$from->value} to {$to->value}.");
    }
}
