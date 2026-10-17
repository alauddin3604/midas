<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Interfaces\Identity\Http\Api\V1\Controllers\LoginController;
use Interfaces\Identity\Http\Api\V1\Controllers\LogoutController;
use Interfaces\Identity\Http\Api\V1\Controllers\RegisterController;
use Interfaces\Identity\Http\Api\V1\Controllers\UpdatePasswordController;

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
