<?php

declare(strict_types=1);

use Domain\Identity\Models\User;
use Domain\Ticketing\Enums\TicketPriority;
use Domain\Ticketing\Enums\TicketStatus;
use Domain\Ticketing\Events\TicketCreated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\postJson;
use function Pest\Laravel\travelTo;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    travelTo('2026-10-29 10:00:00');
});

function validCreateTicketPayload(array $overrides = []): array
{
    return [
        'subject' => 'Cannot connect to VPN',
        'description' => 'VPN client times out on login.',
        'priority' => 'high',
        ...$overrides,
    ];
}

function actingAsRequester(): User
{
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    return $user;
}

dataset('invalid create ticket payloads', [
    'missing subject' => [['subject' => null], 'subject'],
    'subject is not a string' => [['subject' => ['x']], 'subject'],
    'subject too long' => [['subject' => str_repeat('a', 256)], 'subject'],
    'missing description' => [['description' => null], 'description'],
    'missing priority' => [['priority' => null], 'priority'],
    'unknown priority' => [['priority' => 'urgent'], 'priority'],
]);

it('creates a ticket for the authenticated user', function (): void {
    $user = actingAsRequester();

    postJson(route('v1.tickets.store'), validCreateTicketPayload())
        ->assertCreated()
        ->assertJsonStructure(['data' => [
            'id', 'number', 'subject', 'description', 'priority', 'status',
            'requester_id', 'assignee_id', 'allowed_transitions', 'created_at',
        ]])
        ->assertJsonPath('data.number', 'TIC-20261029-000001')
        ->assertJsonPath('data.subject', 'Cannot connect to VPN')
        ->assertJsonPath('data.description', 'VPN client times out on login.')
        ->assertJsonPath('data.priority', 'high')
        ->assertJsonPath('data.status', 'new')
        ->assertJsonPath('data.requester_id', $user->id)
        ->assertJsonPath('data.assignee_id', null)
        ->assertJsonPath('data.allowed_transitions', ['assigned', 'closed'])
        ->assertJsonPath('data.status_histories', [
            [
                'from_status' => null,
                'to_status' => TicketStatus::New->value,
                'changed_by' => $user->id,
                'created_at' => now()->toIso8601String(),
            ],
        ]);

    assertDatabaseHas('tickets', [
        'number' => 'TIC-20261029-000001',
        'status' => 'new',
        'requester_id' => $user->id,
    ]);
});

it('accepts every priority', function (TicketPriority $priority): void {
    actingAsRequester();

    postJson(route('v1.tickets.store'), validCreateTicketPayload(['priority' => $priority->value]))
        ->assertCreated()
        ->assertJsonPath('data.priority', $priority->value);
})->with(TicketPriority::cases());

it('ignores client-supplied requester, assignee and status', function (): void {
    $user = actingAsRequester();
    $other = User::factory()->create();

    postJson(route('v1.tickets.store'), validCreateTicketPayload([
        'requester_id' => $other->id,
        'assignee_id' => $other->id,
        'status' => 'closed',
    ]))
        ->assertCreated()
        ->assertJsonPath('data.requester_id', $user->id)
        ->assertJsonPath('data.assignee_id', null)
        ->assertJsonPath('data.status', 'new');
});

it('records the initial status history', function (): void {
    $user = actingAsRequester();

    postJson(route('v1.tickets.store'), validCreateTicketPayload())->assertCreated();

    assertDatabaseCount('ticket_status_histories', 1);
    assertDatabaseHas('ticket_status_histories', [
        'from_status' => null,
        'to_status' => 'new',
        'changed_by' => $user->id,
    ]);
});

it('dispatches TicketCreated', function (): void {
    Event::fake([TicketCreated::class]);
    actingAsRequester();

    $response = postJson(route('v1.tickets.store'), validCreateTicketPayload())->assertCreated();

    Event::assertDispatched(
        TicketCreated::class,
        fn (TicketCreated $event): bool => $event->ticketId === $response->json('data.id'),
    );
});

it('rejects unauthenticated requests', function (): void {
    postJson(route('v1.tickets.store'), validCreateTicketPayload())
        ->assertUnauthorized();

    assertDatabaseCount('tickets', 0);
});

it('rejects invalid input', function (array $overrides, string $field): void {
    actingAsRequester();

    $response = postJson(route('v1.tickets.store'), validCreateTicketPayload($overrides))
        ->assertUnprocessable()
        ->assertJsonPath('message', 'Validation failed.');

    expect(collect($response->json('errors'))->pluck('field')->all())->toContain($field);

    assertDatabaseCount('tickets', 0);
})->with('invalid create ticket payloads');
