<?php

declare(strict_types=1);

namespace Domain\Ticketing\Models;

use Carbon\CarbonImmutable;
use Domain\Ticketing\Casts\TicketNumberCast;
use Domain\Ticketing\Enums\TicketPriority;
use Domain\Ticketing\Enums\TicketStatus;
use Domain\Ticketing\ValueObjects\TicketNumber;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property TicketNumber $number
 * @property string $subject
 * @property string $description
 * @property TicketPriority $priority
 * @property TicketStatus $status
 * @property string $requester_id
 * @property ?string $assignee_id
 * @property ?CarbonImmutable $resolved_at
 * @property ?CarbonImmutable $closed_at
 * @property ?CarbonImmutable $created_at
 * @property ?CarbonImmutable $updated_at
 */
final class Ticket extends Model
{
    use HasUuids;

    protected function casts(): array
    {
        return [
            'number' => TicketNumberCast::class,
            'status' => TicketStatus::class,
            'priority' => TicketPriority::class,
            'resolved_at' => 'immutable_datetime',
            'closed_at' => 'immutable_datetime',
        ];
    }

    /**
     * @return HasMany<TicketStatusHistory, $this>
     */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(TicketStatusHistory::class);
    }
}
