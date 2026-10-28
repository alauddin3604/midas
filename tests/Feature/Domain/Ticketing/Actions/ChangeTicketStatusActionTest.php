<?php

declare(strict_types=1);

use Domain\Identity\Models\User;
use Domain\Ticketing\Actions\ChangeTicketStatusAction;
use Domain\Ticketing\Enums\TicketStatus;
use Domain\Ticketing\Events\TicketStatusChanged;
use Domain\Ticketing\Exceptions\InvalidStatusTransition;
use Domain\Ticketing\Exceptions\TicketNotAssigned;
use Domain\Ticketing\Models\Ticket;
use Domain\Ticketing\Payloads\ChangeTicketStatusPayload;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;

use function Pest\Laravel\travelTo;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    travelTo('2026-10-28 10:00:00');
});

/** The business rules, written out explicitly on purpose. */
function validTransitions(): array
{
    return [
        [TicketStatus::New, TicketStatus::Assigned],
        [TicketStatus::New, TicketStatus::Closed],
        [TicketStatus::Assigned, TicketStatus::InProgress],
        [TicketStatus::Assigned, TicketStatus::Closed],
        [TicketStatus::InProgress, TicketStatus::OnHold],
        [TicketStatus::InProgress, TicketStatus::Resolved],
        [TicketStatus::OnHold, TicketStatus::InProgress],
        [TicketStatus::Resolved, TicketStatus::Closed],
        [TicketStatus::Resolved, TicketStatus::InProgress], // reopen
    ];
}

dataset('valid transitions', function () {
    foreach (validTransitions() as [$from, $to]) {
        yield "{$from->value} → {$to->value}" => [$from, $to];
    }
});

dataset('invalid transitions', function () {
    foreach (TicketStatus::cases() as $from) {
        foreach (TicketStatus::cases() as $to) {
            if (in_array([$from, $to], validTransitions(), true)) {
                continue;
            }

            yield "{$from->value} → {$to->value}" => [$from, $to];
        }
    }
});

function runChangeStatus(Ticket $ticket, TicketStatus $to, ?string $actorId = null): Ticket
{
    return app(ChangeTicketStatusAction::class)->execute(
        new ChangeTicketStatusPayload(
            ticketId: $ticket->id,
            to: $to,
            actorId: $actorId ?? $ticket->assignee_id ?? $ticket->requester_id,
        ),
    );
}

it('allows valid transitions', function (TicketStatus $from, TicketStatus $to): void {
    $ticket = ticketInStatus($from);

    $updated = runChangeStatus($ticket, $to);

    expect($updated->status)->toBe($to)
        ->and($ticket->fresh()->status)->toBe($to);
})->with('valid transitions');

it('rejects invalid transitions without side effects', function (TicketStatus $from, TicketStatus $to): void {
    Event::fake([TicketStatusChanged::class]);
    $ticket = ticketInStatus($from);

    expect(fn () => runChangeStatus($ticket, $to))
        ->toThrow(InvalidStatusTransition::class);

    expect($ticket->fresh()->status)->toBe($from)
        ->and($ticket->statusHistories()->count())->toBe(0);

    Event::assertNotDispatched(TicketStatusChanged::class);
})->with('invalid transitions');

it('records a status history entry', function (): void {
    $ticket = ticketInStatus(TicketStatus::Assigned);
    $actorId = User::factory()->create()->id;

    runChangeStatus($ticket, TicketStatus::InProgress, $actorId);

    $history = $ticket->statusHistories()->sole();

    expect($history->from_status)->toBe(TicketStatus::Assigned)
        ->and($history->to_status)->toBe(TicketStatus::InProgress)
        ->and($history->changed_by)->toBe($actorId)
        ->and(Carbon::parse($history->created_at)->toDateTimeString())->toBe('2026-10-28 10:00:00');
});

it('dispatches TicketStatusChanged with a snapshot of the change', function (): void {
    Event::fake([TicketStatusChanged::class]);
    $ticket = ticketInStatus(TicketStatus::InProgress);
    $actorId = $ticket->assignee_id;

    runChangeStatus($ticket, TicketStatus::OnHold, $actorId);

    Event::assertDispatchedTimes(TicketStatusChanged::class, 1);
    Event::assertDispatched(TicketStatusChanged::class, fn (TicketStatusChanged $event): bool => $event->ticketId === $ticket->id
        && $event->ticketNumber === $ticket->number->value()
        && $event->from === TicketStatus::InProgress
        && $event->to === TicketStatus::OnHold
        && $event->actorId === $actorId
        && $event->assigneeId === $ticket->assignee_id
        && $event->occurredAt->format('Y-m-d H:i:s') === '2026-10-28 10:00:00');
});

it('sets resolved_at when the ticket is resolved', function (): void {
    $ticket = ticketInStatus(TicketStatus::InProgress);

    $updated = runChangeStatus($ticket, TicketStatus::Resolved);

    expect($updated->resolved_at->toDateTimeString())->toBe('2026-10-28 10:00:00')
        ->and($updated->closed_at)->toBeNull();
});

it('clears resolved_at when a resolved ticket is reopened', function (): void {
    $ticket = ticketInStatus(TicketStatus::InProgress);
    runChangeStatus($ticket, TicketStatus::Resolved);

    $reopened = runChangeStatus($ticket->fresh(), TicketStatus::InProgress);

    expect($reopened->status)->toBe(TicketStatus::InProgress)
        ->and($reopened->resolved_at)->toBeNull();
});

it('sets closed_at when the ticket is closed', function (): void {
    $ticket = ticketInStatus(TicketStatus::Resolved);

    $closed = runChangeStatus($ticket, TicketStatus::Closed);

    expect($closed->closed_at->toDateTimeString())->toBe('2026-10-28 10:00:00');
});

it('requires an assignee before moving to a working status', function (): void {
    Event::fake([TicketStatusChanged::class]);
    $ticket = ticketInStatus(TicketStatus::New, assigned: false);

    expect(fn () => runChangeStatus($ticket, TicketStatus::Assigned))
        ->toThrow(TicketNotAssigned::class);

    expect($ticket->fresh()->status)->toBe(TicketStatus::New);

    Event::assertNotDispatched(TicketStatusChanged::class);
});

it('can close a ticket that has no assignee', function (): void {
    $ticket = ticketInStatus(TicketStatus::New, assigned: false);

    $closed = runChangeStatus($ticket, TicketStatus::Closed, $ticket->requester_id);

    expect($closed->status)->toBe(TicketStatus::Closed);
});

it('fails when the ticket does not exist', function (): void {
    $payload = new ChangeTicketStatusPayload(
        ticketId: '01JXXXXXXXXXXXXXXXXXXXXXXX',
        to: TicketStatus::Closed,
        actorId: User::factory()->create()->id,
    );

    expect(fn () => app(ChangeTicketStatusAction::class)->execute($payload))
        ->toThrow(ModelNotFoundException::class);
});
