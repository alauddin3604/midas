<?php

declare(strict_types=1);

namespace Domain\Ticketing\Events;

use DateTimeImmutable;
use Domain\Ticketing\Enums\TicketPriority;
use Domain\Ticketing\Models\Ticket;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;

final readonly class TicketCreated implements ShouldDispatchAfterCommit
{
    public function __construct(
        public string $ticketId,
        public string $ticketNumber,
        public string $subject,
        public TicketPriority $priority,
        public string $requesterId,
        public DateTimeImmutable $occurredAt,
    ) {}

    public static function fromTicket(Ticket $ticket): self
    {
        return new self(
            ticketId: $ticket->id,
            ticketNumber: $ticket->number->value(),
            subject: $ticket->subject,
            priority: $ticket->priority,
            requesterId: $ticket->requester_id,
            occurredAt: now()->toImmutable(),
        );
    }
}
