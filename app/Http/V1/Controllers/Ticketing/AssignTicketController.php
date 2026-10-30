<?php

declare(strict_types=1);

namespace App\Http\V1\Controllers\Ticketing;

use App\Http\V1\Controllers\ApiController;
use App\Http\V1\Requests\Ticketing\AssignTicketRequest;
use App\Http\V1\Resources\Ticketing\TicketResource;
use Domain\Ticketing\Actions\AssignTicketAction;
use Domain\Ticketing\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Attributes\Controllers\Authorize;

final class AssignTicketController extends ApiController
{
    #[Authorize('assign', 'ticket')]
    public function __invoke(AssignTicketRequest $request, AssignTicketAction $action, Ticket $ticket): JsonResponse
    {
        $ticket = $action->execute($request->payload());

        return TicketResource::make($ticket->load('statusHistories'))
            ->response($request);
    }
}
