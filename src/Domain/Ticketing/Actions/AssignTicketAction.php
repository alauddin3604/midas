<?php

declare(strict_types=1);

namespace Domain\Ticketing\Actions;

use Domain\Ticketing\Enums\TicketStatus;
use Domain\Ticketing\Events\TicketAssigned;
use Domain\Ticketing\Exceptions\TicketAlreadyClosed;
use Domain\Ticketing\Models\Ticket;
use Domain\Ticketing\Payloads\AssignTicketPayload;
use Domain\Ticketing\Payloads\CreateTicketStatusHistoryPayload;
use Domain\Ticketing\Tasks\ChangeTicketStatusTask;
use Illuminate\Support\Facades\DB;

final readonly class AssignTicketAction
{
    public function __construct(
        private ChangeTicketStatusTask $changeTicketStatus,
        private CreateTicketStatusHistoryAction $createStatusHistory,
    ) {}

    /**
     * Assign a ticket, advancing new tickets to assigned.
     */
    public function execute(AssignTicketPayload $payload): Ticket
    {
        return DB::transaction(function () use ($payload): Ticket {
            $ticket = Ticket::query()->lockForUpdate()->findOrFail($payload->ticketId);
            $from = $ticket->status;
            $previousAssigneeId = $ticket->assignee_id;

            if ($ticket->status->isTerminal()) {
                throw TicketAlreadyClosed::create();
            }

            $ticket->assignee_id = $payload->assigneeId;

            if ($ticket->status === TicketStatus::New) {
                $to = TicketStatus::Assigned;
                $this->changeTicketStatus->execute($ticket, $to);
            }

            $ticket->save();

            if ($ticket->status !== $from) {
                $this->createStatusHistory->execute(new CreateTicketStatusHistoryPayload(
                    ticketId: $ticket->id,
                    fromStatus: $from,
                    toStatus: $ticket->status,
                    changedBy: $payload->actorId,
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
