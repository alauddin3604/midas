<?php

declare(strict_types=1);

use App\Http\V1\Controllers\Ticketing\AssignTicketController;
use App\Http\V1\Controllers\Ticketing\CreateTicketController;
use Illuminate\Support\Facades\Route;

Route::post('tickets', CreateTicketController::class)
    ->name('tickets.store');

Route::put('tickets/{ticket}/assignee', AssignTicketController::class)
    ->whereUuid('ticket')
    ->name('tickets.assign');
