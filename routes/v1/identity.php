<?php

declare(strict_types=1);

use App\Http\Api\V1\Controllers\Identity\LoginController;
use App\Http\Api\V1\Controllers\Identity\LogoutController;
use App\Http\Api\V1\Controllers\Identity\RegisterController;
use App\Http\Api\V1\Controllers\Identity\UpdatePasswordController;
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

Route::put('password', UpdatePasswordController::class)
    ->middleware('auth:sanctum')
    ->name('password.update');
