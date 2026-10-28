<?php

declare(strict_types=1);

use Domain\Ticketing\ValueObjects\TicketNumber;

it('throws an exception if the ticket number is incorrect format', function (string $ticketNumber): void {
    expect(fn (): TicketNumber => TicketNumber::fromString($ticketNumber))
        ->toThrow(
            InvalidArgumentException::class,
            'Invalid ticket number: '.$ticketNumber,
        );
})->with([
    'TICC-20260101-000001',
    'TI-20260101-000001',
    '-20260101-000001',
    'TIC-2026-000001',
    'TIC-000001',
    'TIC-20260101',
]);

it('throws an exception if sequence is 0 or less', function (int $sequence): void {
    TicketNumber::generate(new DateTimeImmutable, $sequence);
})->with([0, -1])->throws(InvalidArgumentException::class, 'Sequence must be 1 or more.');

it('can be casted as string', function (): void {
    $ticketNumber = TicketNumber::fromString('TIC-20260101-000001');

    expect((string) $ticketNumber)->toEqual('TIC-20260101-000001');
});

it('compares two ticket numbers', function (): void {
    $ticketNumber = TicketNumber::fromString('TIC-20260101-000001');

    expect($ticketNumber->equals(TicketNumber::fromString('TIC-20260101-000001')))
        ->toBeTrue();
});
