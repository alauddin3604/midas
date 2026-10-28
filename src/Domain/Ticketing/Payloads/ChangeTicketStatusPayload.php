<?php

declare(strict_types=1);

namespace Domain\Ticketing\Payloads;

use Domain\Ticketing\Enums\TicketStatus;

final readonly class ChangeTicketStatusPayload
{
    public function __construct(
        public string $ticketId,
        public TicketStatus $to,
        public string $actorId,
    ) {}
}
