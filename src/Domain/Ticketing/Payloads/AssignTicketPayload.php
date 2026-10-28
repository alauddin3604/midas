<?php

declare(strict_types=1);

namespace Domain\Ticketing\Payloads;

final readonly class AssignTicketPayload
{
    public function __construct(
        public string $ticketId,
        public string $assigneeId,
        public string $actorId,
    ) {}
}
