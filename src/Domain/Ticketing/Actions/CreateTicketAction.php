<?php

declare(strict_types=1);

namespace Domain\Ticketing\Actions;

use Domain\Ticketing\Enums\TicketStatus;
use Domain\Ticketing\Events\TicketCreated;
use Domain\Ticketing\Models\Ticket;
use Domain\Ticketing\Payloads\CreateTicketPayload;
use Domain\Ticketing\Payloads\CreateTicketStatusHistoryPayload;
use Illuminate\Support\Facades\DB;

final readonly class CreateTicketAction
{
    public function __construct(
        private GenerateTicketNumberAction $generateTicketNumber,
        private CreateTicketStatusHistoryAction $createStatusHistory,
    ) {}

    public function execute(CreateTicketPayload $payload): Ticket
    {
        return DB::transaction(function () use ($payload): Ticket {
            $ticket = $this->createTicket($payload);

            $this->createStatusHistory->execute(new CreateTicketStatusHistoryPayload(
                $ticket->id,
                null,
                TicketStatus::New,
                $ticket->requester_id,
            ));

            event(TicketCreated::fromTicket($ticket));

            return $ticket;
        });
    }

    private function createTicket(CreateTicketPayload $payload): Ticket
    {
        $ticket = new Ticket;
        $ticket->number = $this->generateTicketNumber->execute(now()->toImmutable());
        $ticket->subject = $payload->subject;
        $ticket->description = $payload->description;
        $ticket->priority = $payload->priority;
        $ticket->status = TicketStatus::New;
        $ticket->requester_id = $payload->requesterId;
        $ticket->save();

        return $ticket;
    }
}
