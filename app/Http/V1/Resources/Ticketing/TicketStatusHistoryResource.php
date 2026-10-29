<?php

declare(strict_types=1);

namespace App\Http\V1\Resources\Ticketing;

use Domain\Ticketing\Models\TicketStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TicketStatusHistory
 */
final class TicketStatusHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'from_status' => $this->from_status?->value,
            'to_status' => $this->to_status->value,
            'changed_by' => $this->changed_by,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
