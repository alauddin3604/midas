<?php

declare(strict_types=1);

use Database\Factories\Ticketing\TicketSequenceFactory;
use Domain\Ticketing\Actions\GenerateTicketNumberAction;
use Illuminate\Support\Facades\Date;

it('can generate a new ticket number for the day', function (): void {
    $date = Date::now();

    $ticketNumber = app(GenerateTicketNumberAction::class)->execute($date->toDateTimeImmutable());

    expect($ticketNumber->value())
        ->toContain('-000001');
});

it('can generate a next ticket number for the current day', function (): void {
    TicketSequenceFactory::new()->create([
        'last_number' => 1,
    ]);

    $date = Date::now();

    $ticketNumber = app(GenerateTicketNumberAction::class)->execute($date->toDateTimeImmutable());

    expect($ticketNumber->value())
        ->toContain('-000002');
});

it('can generate a new ticket number for the next day', function (): void {
    TicketSequenceFactory::new()->create([
        'date' => Date::now()->subDay()->toDateString(),
        'last_number' => 1,
    ]);

    $date = Date::now();

    $ticketNumber = app(GenerateTicketNumberAction::class)->execute($date->toDateTimeImmutable());

    expect($ticketNumber->value())
        ->toContain('-000001');
});
