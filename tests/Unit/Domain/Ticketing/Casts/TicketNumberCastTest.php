<?php

declare(strict_types=1);

use Domain\Ticketing\Casts\TicketNumberCast;
use Illuminate\Database\Eloquent\Model;

it('set the value to null', function (): void {
    $model = new class extends Model {};
    $cast = new TicketNumberCast;

    expect($cast->set($model, 'number', null, []))
        ->toBeNull();
});

it('set the value from string', function (): void {
    $model = new class extends Model {};
    $cast = new TicketNumberCast;

    expect($cast->set($model, 'number', 'TIC-20260101-000001', []))
        ->toBe('TIC-20260101-000001');
});
