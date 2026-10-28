<?php

declare(strict_types=1);

namespace Domain\Ticketing\Models;

use Carbon\CarbonImmutable;
use Domain\Ticketing\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property string $ticket_id
 * @property ?TicketStatus $from_status
 * @property TicketStatus $to_status
 * @property string $changed_by
 * @property CarbonImmutable $created_at
 */
#[Fillable([
    'from_status',
    'to_status',
    'changed_by',
    'created_at',
])]
#[WithoutTimestamps]
class TicketStatusHistory extends Model
{
    #[Override]
    protected function casts(): array
    {
        return [
            'from_status' => TicketStatus::class,
            'to_status' => TicketStatus::class,
            'created_at' => 'immutable_datetime',
        ];
    }
}
