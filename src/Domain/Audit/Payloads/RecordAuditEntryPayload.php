<?php

declare(strict_types=1);

namespace Domain\Audit\Payloads;

use DateTimeInterface;
use Illuminate\Contracts\Support\Arrayable;
use Override;

/**
 * @implements Arrayable<string, mixed>
 */
final readonly class RecordAuditEntryPayload implements Arrayable
{
    /**
     * @param  array<string, array{old: mixed, new: mixed}>|null  $changes
     * @param  array<string, mixed>|null  $context
     */
    public function __construct(
        public string $event,
        public string $subjectType,
        public string $subjectId,
        public DateTimeInterface $occurredAt,
        public ?string $actorId = null,
        public ?array $changes = null,
        public ?array $context = null,
    ) {}

    /**
     * @return array{
     *      event: string,
     *      actor_id: string|null,
     *      subject_type: string,
     *      subject_id: string,
     *      changes: array<string, array{old: mixed, new: mixed}>|null,
     *      context: array<string, mixed>|null,
     *      occurred_at: DateTimeInterface
     * }
     */
    #[Override]
    public function toArray(): array
    {
        return [
            'event' => $this->event,
            'actor_id' => $this->actorId,
            'subject_type' => $this->subjectType,
            'subject_id' => $this->subjectId,
            'changes' => $this->changes,
            'context' => $this->context,
            'occurred_at' => $this->occurredAt,
        ];
    }
}
