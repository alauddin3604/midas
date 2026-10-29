<?php

declare(strict_types=1);

use App\Http\V1\Controllers\Identity\ListUsersController;
use App\Http\V1\Controllers\Identity\LoginController;
use App\Http\V1\Controllers\Identity\LogoutController;
use App\Http\V1\Controllers\Identity\RegisterController;
use App\Http\V1\Controllers\Identity\UpdatePasswordController;
use App\Http\V1\Controllers\Identity\UpdateUserController;
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

Route::prefix('users')
    ->name('users.')
    ->middleware('auth:sanctum')
    ->group(function (): void {
        Route::get('/', ListUsersController::class)
            ->name('index');

        Route::put('{user}', UpdateUserController::class)
            ->name('update');
    });
