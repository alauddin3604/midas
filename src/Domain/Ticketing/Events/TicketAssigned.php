<?php

declare(strict_types=1);

namespace Domain\Ticketing\Events;

use DateTimeImmutable;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;

final readonly class TicketAssigned implements ShouldDispatchAfterCommit
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
