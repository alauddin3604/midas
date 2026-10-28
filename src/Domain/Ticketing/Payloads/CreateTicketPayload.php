<?php

declare(strict_types=1);

namespace Domain\Ticketing\Payloads;

use Domain\Ticketing\Enums\TicketPriority;

final readonly class CreateTicketPayload
{
    public function __construct(
        public string $subject,
        public string $description,
        public TicketPriority $priority,
        public string $requesterId,
    ) {}
}
