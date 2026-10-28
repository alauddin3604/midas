<?php

declare(strict_types=1);

use Domain\Identity\Models\User;
use Domain\Ticketing\Actions\AssignTicketAction;
use Domain\Ticketing\Enums\TicketStatus;
use Domain\Ticketing\Events\TicketAssigned;
use Domain\Ticketing\Exceptions\TicketAlreadyClosed;
use Domain\Ticketing\Models\Ticket;
use Domain\Ticketing\Payloads\AssignTicketPayload;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\travelTo;

beforeEach(function (): void {
    travelTo('2026-10-28 10:00:00');
});

function runAssign(Ticket $ticket, string $assigneeId, ?string $actorId = null): Ticket
{
    return app(AssignTicketAction::class)->execute(
        new AssignTicketPayload(
            ticketId: $ticket->id,
            assigneeId: $assigneeId,
            actorId: $actorId ?? $assigneeId,
        ),
    );
}

dataset('statuses that stay unchanged on reassignment', [
    'assigned' => [TicketStatus::Assigned],
    'in progress' => [TicketStatus::InProgress],
    'on hold' => [TicketStatus::OnHold],
    'resolved' => [TicketStatus::Resolved],
]);

it('assigns a new ticket and moves it to assigned', function (): void {
    $ticket = ticketInStatus(TicketStatus::New, assigned: false);
    $assignee = User::factory()->create();

    $updated = runAssign($ticket, $assignee->id);

    expect($updated->assignee_id)->toBe($assignee->id)
        ->and($updated->status)->toBe(TicketStatus::Assigned);

    assertDatabaseHas('tickets', [
        'id' => $ticket->id,
        'assignee_id' => $assignee->id,
        'status' => 'assigned',
    ]);
});

it('records a status history entry when a new ticket gets assigned', function (): void {
    $ticket = ticketInStatus(TicketStatus::New, assigned: false);
    $assignee = User::factory()->create();
    $actor = User::factory()->create();

    runAssign($ticket, $assignee->id, $actor->id);

    $history = $ticket->statusHistories()->sole();

    expect($history->from_status)->toBe(TicketStatus::New)
        ->and($history->to_status)->toBe(TicketStatus::Assigned)
        ->and($history->changed_by)->toBe($actor->id)
        ->and(Carbon::parse($history->created_at)->toDateTimeString())->toBe('2026-10-28 10:00:00');
});

it('reassigns without changing the status', function (TicketStatus $status): void {
    $ticket = ticketInStatus($status);
    $newAssignee = User::factory()->create();

    $updated = runAssign($ticket, $newAssignee->id);

    expect($updated->assignee_id)->toBe($newAssignee->id)
        ->and($updated->status)->toBe($status)
        ->and($ticket->fresh()->assignee_id)->toBe($newAssignee->id);
})->with('statuses that stay unchanged on reassignment');

it('does not write status history when only the assignee changes', function (): void {
    $ticket = ticketInStatus(TicketStatus::InProgress);

    runAssign($ticket, User::factory()->create()->id);

    expect($ticket->statusHistories()->count())->toBe(0);
});

it('dispatches TicketAssigned for a first assignment', function (): void {
    Event::fake([TicketAssigned::class]);
    $ticket = ticketInStatus(TicketStatus::New, assigned: false);
    $assignee = User::factory()->create();
    $actor = User::factory()->create();

    runAssign($ticket, $assignee->id, $actor->id);

    Event::assertDispatchedTimes(TicketAssigned::class, 1);
    Event::assertDispatched(TicketAssigned::class, fn (TicketAssigned $event): bool => $event->ticketId === $ticket->id
        && $event->ticketNumber === $ticket->number->value()
        && $event->assigneeId === $assignee->id
        && $event->previousAssigneeId === null
        && $event->actorId === $actor->id
        && $event->occurredAt->format('Y-m-d H:i:s') === '2026-10-28 10:00:00');
});

it('includes the previous assignee in the event on reassignment', function (): void {
    Event::fake([TicketAssigned::class]);
    $ticket = ticketInStatus(TicketStatus::InProgress);
    $previousAssigneeId = $ticket->assignee_id;
    $newAssignee = User::factory()->create();

    runAssign($ticket, $newAssignee->id);

    Event::assertDispatched(TicketAssigned::class, fn (TicketAssigned $event): bool => $event->assigneeId === $newAssignee->id
        && $event->previousAssigneeId === $previousAssigneeId);
});

it('rejects assigning a closed ticket without side effects', function (): void {
    Event::fake([TicketAssigned::class]);
    $ticket = ticketInStatus(TicketStatus::Closed);
    $originalAssigneeId = $ticket->assignee_id;

    expect(fn () => runAssign($ticket, User::factory()->create()->id))
        ->toThrow(TicketAlreadyClosed::class);

    expect($ticket->fresh()->assignee_id)->toBe($originalAssigneeId)
        ->and($ticket->statusHistories()->count())->toBe(0);

    Event::assertNotDispatched(TicketAssigned::class);
});

it('fails when the ticket does not exist', function (): void {
    $payload = new AssignTicketPayload(
        ticketId: '01JXXXXXXXXXXXXXXXXXXXXXXX',
        assigneeId: User::factory()->create()->id,
        actorId: User::factory()->create()->id,
    );

    expect(fn () => app(AssignTicketAction::class)->execute($payload))
        ->toThrow(ModelNotFoundException::class);
});
