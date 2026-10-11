<?php

declare(strict_types=1);

use App\Identity\Http\Api\V1\Controllers\LoginController;
use App\Identity\Http\Api\V1\Controllers\LogoutController;
use App\Identity\Http\Api\V1\Controllers\RegisterController;
use Illuminate\Support\Facades\Route;

Route::post('register', RegisterController::class)
    ->middleware('throttle:5,1')
    ->name('register');

Route::post('login', LoginController::class)
    ->middleware('throttle:5,1')
    ->name('login');

Route::post('logout', LogoutController::class)
    ->middleware('auth:sanctum')
    ->name('logout');
