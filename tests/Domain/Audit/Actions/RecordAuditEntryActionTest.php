<?php

declare(strict_types=1);

use Domain\Audit\Actions\RecordAuditEntryAction;
use Domain\Audit\Payloads\RecordAuditEntryPayload;

use function Pest\Laravel\assertDatabaseHas;

it('records an entry with every field from the payload', function (): void {
    $occurredAt = now()->subMinute()->startOfSecond();

    $entry = app(RecordAuditEntryAction::class)->execute(new RecordAuditEntryPayload(
        event: 'identity.user.updated',
        subjectType: 'user',
        subjectId: 'subject-1',
        occurredAt: $occurredAt,
        actorId: 'actor-1',
        changes: ['role' => ['old' => 'member', 'new' => 'admin']],
        context: ['ip' => '127.0.0.1'],
    ));

    assertDatabaseHas('audit_entries', [
        'id' => $entry->id,
        'event' => 'identity.user.updated',
        'actor_id' => 'actor-1',
        'subject_type' => 'user',
        'subject_id' => 'subject-1',
    ]);

    expect($entry->refresh())
        ->changes->toBe(['role' => ['old' => 'member', 'new' => 'admin']])
        ->context->toBe(['ip' => '127.0.0.1'])
        ->occurred_at->equalTo($occurredAt)->toBeTrue();
});

it('records an entry without an actor, changes or context', function (): void {
    $entry = app(RecordAuditEntryAction::class)->execute(new RecordAuditEntryPayload(
        event: 'identity.user.authenticated',
        subjectType: 'user',
        subjectId: 'subject-1',
        occurredAt: now(),
    ));

    expect($entry->refresh())
        ->actor_id->toBeNull()
        ->changes->toBeNull()
        ->context->toBeNull();
});
