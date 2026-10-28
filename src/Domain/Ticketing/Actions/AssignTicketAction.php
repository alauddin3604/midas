<?php

declare(strict_types=1);

namespace Domain\Ticketing\Actions;

use Domain\Ticketing\Events\TicketAssigned;
use Domain\Ticketing\Models\Ticket;
use Domain\Ticketing\Payloads\AssignTicketPayload;
use Domain\Ticketing\Payloads\CreateTicketStatusHistoryPayload;
use Illuminate\Support\Facades\DB;

final readonly class AssignTicketAction
{
    public function __construct(
        private CreateTicketStatusHistoryAction $createStatusHistory,
    ) {}

    public function execute(AssignTicketPayload $payload): Ticket
    {
        return DB::transaction(function () use ($payload): Ticket {
            $ticket = Ticket::query()->lockForUpdate()->findOrFail($payload->ticketId);
            $from = $ticket->status;
            $previousAssigneeId = $ticket->assignee_id;

            $ticket->assignTo($payload->assigneeId);
            $ticket->save();

            if ($ticket->status !== $from) {
                $this->createStatusHistory->execute(new CreateTicketStatusHistoryPayload(
                    $ticket->id,
                    $from,
                    $ticket->status,
                    $payload->actorId,
                ));
            }

            event(new TicketAssigned(
                ticketId: $ticket->id,
                ticketNumber: $ticket->number->value(),
                assigneeId: $payload->assigneeId,
                previousAssigneeId: $previousAssigneeId,
                actorId: $payload->actorId,
                occurredAt: now()->toImmutable(),
            ));

            return $ticket;
        });
    }
}
