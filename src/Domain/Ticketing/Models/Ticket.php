<?php

declare(strict_types=1);

namespace Domain\Ticketing\Models;

use Carbon\CarbonImmutable;
use Domain\Ticketing\Casts\TicketNumberCast;
use Domain\Ticketing\Enums\TicketPriority;
use Domain\Ticketing\Enums\TicketStatus;
use Domain\Ticketing\Exceptions\InvalidStatusTransition;
use Domain\Ticketing\Exceptions\TicketAlreadyClosed;
use Domain\Ticketing\Exceptions\TicketNotAssigned;
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

    /**
     * Assign the ticket to a user.
     */
    public function assignTo(string $assigneeId): void
    {
        if ($this->status->isTerminal()) {
            throw TicketAlreadyClosed::create();
        }

        $this->assignee_id = $assigneeId;

        if ($this->status === TicketStatus::New) {
            $this->changeStatus(TicketStatus::Assigned);
        }
    }

    /**
     * Change the ticket status.
     */
    public function changeStatus(TicketStatus $to): void
    {
        if (! $this->status->canTransitionTo($to)) {
            throw InvalidStatusTransition::between($this->status, $to);
        }

        if ($to->requiresAssignee() && $this->assignee_id === null) {
            throw TicketNotAssigned::forStatus($to);
        }

        $from = $this->status;
        $this->status = $to;

        match (true) {
            $to->hasResolved() => $this->resolved_at = now()->toImmutable(),
            $to === TicketStatus::Closed => $this->closed_at = now()->toImmutable(),
            $from->hasResolved() && $to === TicketStatus::InProgress => $this->resolved_at = null,
            default => null,
        };
    }
}
