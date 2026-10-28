<?php

declare(strict_types=1);

namespace Domain\Ticketing\Actions;

use Domain\Ticketing\Models\TicketStatusHistory;
use Domain\Ticketing\Payloads\CreateTicketStatusHistoryPayload;

final readonly class CreateTicketStatusHistoryAction
{
    public function execute(CreateTicketStatusHistoryPayload $payload): TicketStatusHistory
    {
        $history = new TicketStatusHistory;
        $history->ticket_id = $payload->ticketId;
        $history->from_status = $payload->fromStatus;
        $history->to_status = $payload->toStatus;
        $history->changed_by = $payload->changedBy;
        $history->created_at = now();
        $history->save();

        return $history;
    }
}
