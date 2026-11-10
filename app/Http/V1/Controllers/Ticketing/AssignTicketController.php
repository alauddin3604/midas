<?php

declare(strict_types=1);

namespace App\Http\V1\Controllers\Ticketing;

use App\Http\Controllers\V1\ApiController;
use App\Http\V1\Requests\Ticketing\AssignTicketRequest;
use App\Http\V1\Resources\Ticketing\TicketResource;
use Domain\Ticketing\Actions\AssignTicketAction;
use Domain\Ticketing\Models\Ticket;
use Illuminate\Routing\Attributes\Controllers\Authorize;
use Symfony\Component\HttpFoundation\Response;

final class AssignTicketController extends ApiController
{
    #[Authorize('assign', 'ticket')]
    public function __invoke(AssignTicketRequest $request, AssignTicketAction $action, Ticket $ticket): Response
    {
        $ticket = $action->execute($request->payload());

        return TicketResource::make($ticket->load('statusHistories'))
            ->response($request);
    }
}
