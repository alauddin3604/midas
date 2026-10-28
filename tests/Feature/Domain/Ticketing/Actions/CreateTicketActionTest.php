<?php

declare(strict_types=1);

use Database\Factories\UserFactory;
use Domain\Identity\Models\User;
use Domain\Ticketing\Actions\CreateTicketAction;
use Domain\Ticketing\Enums\TicketPriority;
use Domain\Ticketing\Enums\TicketStatus;
use Domain\Ticketing\Events\TicketCreated;
use Domain\Ticketing\Models\Ticket;
use Domain\Ticketing\Models\TicketSequence;
use Domain\Ticketing\Payloads\CreateTicketPayload;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Event;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\travelTo;

function ticketPayload(string $requesterId, array $overrides = []): CreateTicketPayload
{
    return new CreateTicketPayload(
        subject: $overrides['subject'] ?? 'Cannot connect to VPN',
        description: $overrides['description'] ?? 'VPN client times out on login.',
        priority: $overrides['priority'] ?? TicketPriority::High,
        requesterId: $requesterId,
    );
}

function createTicket(User $requester, array $overrides = []): Ticket
{
    return app(CreateTicketAction::class)->execute(ticketPayload($requester->id, $overrides));
}

it('creates a new ticket for the requester', function (): void {
    $user = UserFactory::new()->create();
    $ticket = createTicket($user);

    expect($ticket->exists)->toBeTrue()
        ->and($ticket->subject)->toBe('Cannot connect to VPN')
        ->and($ticket->description)->toBe('VPN client times out on login.')
        ->and($ticket->priority)->toBe(TicketPriority::High)
        ->and($ticket->status)->toBe(TicketStatus::New)
        ->and($ticket->requester_id)->toBe($user->id)
        ->and($ticket->assignee_id)->toBeNull();

    assertDatabaseHas('tickets', [
        'id' => $ticket->id,
        'status' => 'new',
        'requester_id' => $user->id,
    ]);
});

it('assigns a ticket number based on the current date and sequence', function () {
    $ticket = createTicket(UserFactory::new()->create());

    expect($ticket->number->value())->toBe('TIC-'.Date::now()->format('Ymd').'-000001');
});

it('increments the sequence for tickets created on the same day', function (): void {
    $requester = UserFactory::new()->create();
    $first = createTicket($requester);
    $second = createTicket($requester);
    $third = createTicket($requester);

    expect($first->number->value())->toBe('TIC-'.Date::now()->format('Ymd').'-000001')
        ->and($second->number->value())->toBe('TIC-'.Date::now()->format('Ymd').'-000002')
        ->and($third->number->value())->toBe('TIC-'.Date::now()->format('Ymd').'-000003');
});

it('restarts the sequence on a new day', function (): void {
    travelTo('2026-10-27 03:00:00');

    $requester = UserFactory::new()->create();
    createTicket($requester);
    createTicket($requester);

    travelTo('2026-10-28 09:00:00');

    $ticket = createTicket($requester);

    expect($ticket->number->value())->toBe('TIC-20261028-000001');
});

it('records the initial status history', function (): void {
    $requester = UserFactory::new()->create();
    $ticket = createTicket($requester);

    $history = $ticket->statusHistories()->get();

    expect($history)->toHaveCount(1)
        ->and($history->first()->from_status)->toBeNull()
        ->and($history->first()->to_status)->toBe(TicketStatus::New)
        ->and($history->first()->changed_by)->toBe($requester->id);
});

it('dispatches the TicketCreated event', function () {
    Event::fake([TicketCreated::class]);

    $ticket = createTicket(UserFactory::new()->create());

    Event::assertDispatched(TicketCreated::class, [
        'ticketId' => $ticket->id,
        'ticketNumber' => $ticket->number->value(),
        'subject' => $ticket->subject,
        'priority' => $ticket->priority,
        'requesterId' => $ticket->requester_id,
    ]);
});

it('does not consume a sequence number when ticket creation fails', function (): void {
    // Force the insert to fail after the number has been generated.
    Ticket::creating(fn () => throw new RuntimeException('boom'));

    expect(fn () => createTicket(UserFactory::new()->create()))
        ->toThrow(RuntimeException::class);

    expect(Ticket::query()->count())->toBe(0)
        ->and(TicketSequence::query()->sum('last_number'))->toBe(0);
});
