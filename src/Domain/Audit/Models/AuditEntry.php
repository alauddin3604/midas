<?php

declare(strict_types=1);

namespace Domain\Audit\Models;

use Database\Factories\AuditEntryFactory;
use Domain\Audit\QueryBuilders\AuditEntryQueryBuilder;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Append-only record of something that happened in the application: entries are never updated.
 *
 * @property string $id
 * @property string $event
 * @property string|null $actor_id
 * @property string $subject_type
 * @property string $subject_id
 * @property array<string, array{old: mixed, new: mixed}>|null $changes
 * @property array<string, mixed>|null $context
 * @property Carbon $occurred_at
 */
#[Table('audit_entries', timestamps: false)]
#[UseFactory(AuditEntryFactory::class)]
#[UseEloquentBuilder(AuditEntryQueryBuilder::class)]
class AuditEntry extends Model
{
    /** @use HasFactory<AuditEntryFactory> */
    use HasFactory, HasUuids, MassPrunable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'changes' => 'array',
            'context' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    /**
     * Get the entries older than the configured retention period.
     */
    public function prunable(): AuditEntryQueryBuilder
    {
        return static::query()->occurredBefore(now()->subDays(config()->integer('audit.retention_days')));
    }
}
