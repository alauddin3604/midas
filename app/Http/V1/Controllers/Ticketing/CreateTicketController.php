<?php

declare(strict_types=1);

namespace App\Http\V1\Controllers\Ticketing;

use App\Http\Controllers\V1\ApiController;
use App\Http\V1\Requests\Ticketing\CreateTicketRequest;
use App\Http\V1\Resources\Ticketing\TicketResource;
use Domain\Ticketing\Actions\CreateTicketAction;
use Domain\Ticketing\Models\Ticket;
use Illuminate\Routing\Attributes\Controllers\Authorize;
use Symfony\Component\HttpFoundation\Response;

final class CreateTicketController extends ApiController
{
    #[Authorize('create', Ticket::class)]
    public function __invoke(CreateTicketRequest $request, CreateTicketAction $action): Response
    {
        $ticket = $action->execute($request->payload());

        return TicketResource::make($ticket->load('statusHistories'))
            ->response($request)
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
