<?php

declare(strict_types=1);

namespace Domain\Ticketing\Actions;

use Domain\Ticketing\Events\TicketStatusChanged;
use Domain\Ticketing\Models\Ticket;
use Domain\Ticketing\Payloads\ChangeTicketStatusPayload;
use Domain\Ticketing\Payloads\CreateTicketStatusHistoryPayload;
use Illuminate\Support\Facades\DB;

final readonly class ChangeTicketStatusAction
{
    public function __construct(
        private CreateTicketStatusHistoryAction $createStatusHistory,
    ) {}

    public function execute(ChangeTicketStatusPayload $payload): Ticket
    {
        return DB::transaction(function () use ($payload): Ticket {
            $ticket = Ticket::query()->lockForUpdate()->findOrFail($payload->ticketId);
            $from = $ticket->status;

            $ticket->changeStatus($payload->to);
            $ticket->save();

            $this->createStatusHistory->execute(new CreateTicketStatusHistoryPayload(
                $ticket->id,
                $from,
                $payload->to,
                $payload->actorId,
            ));

            event(new TicketStatusChanged(
                $ticket->id,
                $ticket->number->value(),
                $from,
                $payload->to,
                $payload->actorId,
                $ticket->assignee_id,
                now()->toImmutable(),
            ));

            return $ticket;
        });
    }
}
