<?php

declare(strict_types=1);

namespace Domain\Ticketing\Payloads;

use Domain\Ticketing\Enums\TicketStatus;

final readonly class CreateTicketStatusHistoryPayload
{
    public function __construct(
        public string $ticketId,
        public ?TicketStatus $fromStatus,
        public TicketStatus $toStatus,
        public string $changedBy,
    ) {}
}
