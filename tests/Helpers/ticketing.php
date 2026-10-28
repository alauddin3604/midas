<?php

declare(strict_types=1);

use Domain\Identity\Models\User;
use Domain\Ticketing\Enums\TicketPriority;
use Domain\Ticketing\Enums\TicketStatus;
use Domain\Ticketing\Models\Ticket;
use Domain\Ticketing\ValueObjects\TicketNumber;
use Illuminate\Support\Facades\Date;

function ticketInStatus(TicketStatus $status, bool $assigned = true): Ticket
{
    static $sequence = 0;

    $ticket = new Ticket;
    $ticket->number = TicketNumber::generate(Date::now(), ++$sequence);
    $ticket->subject = 'Printer is offline';
    $ticket->description = 'Third floor printer shows offline.';
    $ticket->priority = TicketPriority::Medium;
    $ticket->status = $status;
    $ticket->requester_id = User::factory()->create()->id;
    $ticket->assignee_id = $assigned ? User::factory()->create()->id : null;
    $ticket->save();

    return $ticket;
}
