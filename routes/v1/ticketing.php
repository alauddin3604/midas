<?php

declare(strict_types=1);

use App\Http\V1\Controllers\Ticketing\CreateTicketController;
use Illuminate\Support\Facades\Route;

Route::post('tickets', CreateTicketController::class)
    ->name('tickets.store');
