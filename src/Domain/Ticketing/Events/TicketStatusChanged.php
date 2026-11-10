<?php

declare(strict_types=1);

namespace Domain\Ticketing\Events;

use App\Events\Event;
use DateTimeImmutable;
use Domain\Ticketing\Enums\TicketStatus;

final readonly class TicketStatusChanged extends Event
{
    public function __construct(
        public string $ticketId,
        public string $ticketNumber,
        public readonly TicketStatus $from,
        public readonly TicketStatus $to,
        public readonly string $actorId,
        public ?string $assigneeId,
        public DateTimeImmutable $occurredAt,
    ) {}
}
