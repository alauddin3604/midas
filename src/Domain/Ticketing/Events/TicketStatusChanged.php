<?php

declare(strict_types=1);

namespace Domain\Ticketing\Events;

use DateTimeImmutable;
use Domain\Ticketing\Enums\TicketStatus;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;

final class TicketStatusChanged implements ShouldDispatchAfterCommit
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
