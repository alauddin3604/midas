<?php

declare(strict_types=1);

namespace App\Http\V1\Resources\Ticketing;

use Domain\Ticketing\Enums\TicketStatus;
use Domain\Ticketing\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Ticket
 */
final class TicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number->value(),
            'subject' => $this->subject,
            'description' => $this->description,
            'priority' => $this->priority->value,
            'status' => $this->status->value,
            'requester_id' => $this->requester_id,
            'assignee_id' => $this->assignee_id,
            'resolved_at' => $this->resolved_at?->toIso8601String(),
            'closed_at' => $this->closed_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'allowed_transitions' => array_map(
                fn (TicketStatus $status): string => $status->value,
                $this->status->allowedTransitions(),
            ),
            'status_histories' => TicketStatusHistoryResource::collection(
                $this->whenLoaded('statusHistories'),
            ),
        ];
    }
}
