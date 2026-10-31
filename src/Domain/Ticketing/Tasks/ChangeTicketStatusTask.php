<?php

declare(strict_types=1);

namespace Domain\Ticketing\Tasks;

use Domain\Ticketing\Enums\TicketStatus;
use Domain\Ticketing\Exceptions\InvalidStatusTransition;
use Domain\Ticketing\Models\Ticket;

final class ChangeTicketStatusTask
{
    /**
     * Assert and change the ticket status.
     */
    public function execute(Ticket $ticket, TicketStatus $to): void
    {
        if (! $ticket->status->canTransitionTo($to)) {
            throw InvalidStatusTransition::between($ticket->status, $to);
        }

        $ticket->status = $to;
    }
}
