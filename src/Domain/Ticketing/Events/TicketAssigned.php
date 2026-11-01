<?php

declare(strict_types=1);

namespace Domain\Ticketing\Events;

use DateTimeImmutable;
use Support\Bases\Events\DomainEvent;

final readonly class TicketAssigned extends DomainEvent
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
