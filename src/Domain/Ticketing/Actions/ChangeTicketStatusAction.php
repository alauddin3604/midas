<?php

declare(strict_types=1);

namespace Domain\Ticketing\Actions;

use Domain\Ticketing\Enums\TicketStatus;
use Domain\Ticketing\Events\TicketStatusChanged;
use Domain\Ticketing\Exceptions\TicketNotAssigned;
use Domain\Ticketing\Models\Ticket;
use Domain\Ticketing\Payloads\ChangeTicketStatusPayload;
use Domain\Ticketing\Payloads\CreateTicketStatusHistoryPayload;
use Illuminate\Support\Facades\DB;

final readonly class ChangeTicketStatusAction
{
    public function __construct(
        private CreateTicketStatusHistoryAction $createStatusHistory,
    ) {}

    /**
     * Change the ticket status and record the transition.
     */
    public function execute(ChangeTicketStatusPayload $payload): Ticket
    {
        return DB::transaction(function () use ($payload): Ticket {
            $ticket = Ticket::query()->lockForUpdate()->findOrFail($payload->ticketId);
            $from = $ticket->status;

            $this->changeStatus($ticket, $payload->to);
            $ticket->save();

            $this->createStatusHistory->execute(new CreateTicketStatusHistoryPayload(
                ticketId: $ticket->id,
                fromStatus: $from,
                toStatus: $payload->to,
                changedBy: $payload->actorId,
            ));

            event(new TicketStatusChanged(
                ticketId: $ticket->id,
                ticketNumber: $ticket->number->value(),
                from: $from,
                to: $payload->to,
                actorId: $payload->actorId,
                assigneeId: $ticket->assignee_id,
                occurredAt: now()->toImmutable(),
            ));

            return $ticket;
        });
    }

    /**
     * Validate the requested transition and update status-related timestamps.
     */
    private function changeStatus(Ticket $ticket, TicketStatus $to): void
    {
        $ticket->status->assertCanTransitionTo($to);

        if ($to->requiresAssignee() && $ticket->assignee_id === null) {
            throw TicketNotAssigned::forStatus($to);
        }

        $from = $ticket->status;
        $ticket->status = $to;

        match (true) {
            $to->hasResolved() => $ticket->resolved_at = now()->toImmutable(),
            $to === TicketStatus::Closed => $ticket->closed_at = now()->toImmutable(),
            $from->hasResolved() && $to === TicketStatus::InProgress => $ticket->resolved_at = null,
            default => null,
        };
    }
}
