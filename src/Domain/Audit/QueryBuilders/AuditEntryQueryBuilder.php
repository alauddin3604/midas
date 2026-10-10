<?php

declare(strict_types=1);

namespace Domain\Audit\QueryBuilders;

use DateTimeInterface;
use Domain\Audit\Models\AuditEntry;
use Illuminate\Database\Eloquent\Builder;

/**
 * @extends Builder<AuditEntry>
 */
final class AuditEntryQueryBuilder extends Builder
{
    /**
     * Match entries made by the given actor.
     */
    public function byActor(?string $actorId): static
    {
        return $this->when(
            $actorId !== null && $actorId !== '',
            fn (Builder $query): Builder => $query->where('actor_id', $actorId),
        );
    }

    /**
     * Match entries of the given event name.
     */
    public function ofEvent(?string $event): static
    {
        return $this->when(
            $event !== null && $event !== '',
            fn (Builder $query): Builder => $query->where('event', $event),
        );
    }

    /**
     * Match entries that occurred between the two moments, either bound being optional.
     */
    public function between(?DateTimeInterface $from, ?DateTimeInterface $until): static
    {
        return $this
            ->when($from, fn (Builder $query, DateTimeInterface $from): Builder => $query->where('occurred_at', '>=', $from))
            ->when($until, fn (Builder $query, DateTimeInterface $until): Builder => $query->where('occurred_at', '<=', $until));
    }
}
