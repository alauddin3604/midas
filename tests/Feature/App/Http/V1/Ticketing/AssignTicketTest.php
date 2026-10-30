<?php

declare(strict_types=1);

use Domain\Identity\Enums\Role;
use Domain\Identity\Models\User;
use Domain\Ticketing\Enums\TicketStatus;
use Domain\Ticketing\Events\TicketAssigned;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\putJson;

uses(RefreshDatabase::class);

function actingAsAdmin(): User
{
    $user = User::factory()->create([
        'role' => Role::Admin,
    ]);
    Sanctum::actingAs($user);

    return $user;
}

it('assigns a new ticket and moves it to assigned', function (): void {
    $staff = actingAsAdmin();
    $assignee = User::factory()->create();
    $ticket = ticketInStatus(TicketStatus::New, assigned: false);

    putJson(route('v1.tickets.assign', $ticket), ['assignee_id' => $assignee->id])
        ->assertOk()
        ->assertJsonPath('data.assignee_id', $assignee->id)
        ->assertJsonPath('data.status', 'assigned')
        ->assertJsonPath('data.allowed_transitions', ['in_progress', 'closed'])
        ->assertJsonCount(1, 'data.status_histories')
        ->assertJsonPath('data.status_histories.0.to_status', 'assigned')
        ->assertJsonPath('data.status_histories.0.changed_by', $staff->id);
});

it('reassigns without changing the status', function (): void {
    actingAsAdmin();
    $assignee = User::factory()->create();
    $ticket = ticketInStatus(TicketStatus::InProgress);

    putJson(route('v1.tickets.assign', $ticket), ['assignee_id' => $assignee->id])
        ->assertOk()
        ->assertJsonPath('data.assignee_id', $assignee->id)
        ->assertJsonPath('data.status', 'in_progress')
        ->assertJsonCount(0, 'data.status_histories');
});

it('dispatches TicketAssigned', function (): void {
    Event::fake([TicketAssigned::class]);
    actingAsAdmin();
    $assignee = User::factory()->create();
    $ticket = ticketInStatus(TicketStatus::New, assigned: false);

    putJson(route('v1.tickets.assign', $ticket), ['assignee_id' => $assignee->id])->assertOk();

    Event::assertDispatched(
        TicketAssigned::class,
        fn (TicketAssigned $event): bool => $event->ticketId === $ticket->id
            && $event->assigneeId === $assignee->id,
    );
});

it('rejects unauthenticated requests', function (): void {
    $ticket = ticketInStatus(TicketStatus::New, assigned: false);

    putJson(route('v1.tickets.assign', $ticket), ['assignee_id' => 1])->assertUnauthorized();
});

it('forbids users who may not assign tickets', function (): void {
    Sanctum::actingAs(User::factory()->create());
    $ticket = ticketInStatus(TicketStatus::New, assigned: false);

    putJson(route('v1.tickets.assign', $ticket), ['assignee_id' => User::factory()->create()->id])
        ->assertForbidden();

    expect($ticket->fresh()->assignee_id)->toBeNull();
});

it('returns 404 for an unknown ticket', function (): void {
    actingAsAdmin();

    putJson(route('v1.tickets.assign', '01JXXXXXXXXXXXXXXXXXXXXXXX'), [
        'assignee_id' => User::factory()->create()->id,
    ])->assertNotFound();
});

it('validates the assignee', function (array $body): void {
    actingAsAdmin();
    $ticket = ticketInStatus(TicketStatus::New, assigned: false);

    $response = putJson(route('v1.tickets.assign', $ticket), $body)->assertUnprocessable();

    expect(collect($response->json('errors'))->pluck('field')->all())->toContain('assignee_id');
})->with([
    'missing' => [[]],
    'not an integer' => [['assignee_id' => 'abc']],
    'unknown user' => [['assignee_id' => 999999]],
]);

it('rejects assigning a closed ticket', function (): void {
    actingAsAdmin();
    $ticket = ticketInStatus(TicketStatus::Closed);

    putJson(route('v1.tickets.assign', $ticket), ['assignee_id' => User::factory()->create()->id])
        ->assertConflict();

    expect($ticket->fresh()->assignee_id)->toBe($ticket->assignee_id);
});
