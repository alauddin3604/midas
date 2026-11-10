<?php

declare(strict_types=1);

namespace Domain\Ticketing\Events;

use App\Events\Event;
use DateTimeImmutable;

final readonly class TicketAssigned extends Event
{
    public function __construct(
        public string $ticketId,
        public string $ticketNumber,
        public string $assigneeId,
        public ?string $previousAssigneeId,
        public string $actorId,
        public DateTimeImmutable $occurredAt,
    ) {}
}
