<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Interface\Identity\Api\V1\Controllers\CreateUserController;
use Interface\Identity\Api\V1\Controllers\DeleteUserController;
use Interface\Identity\Api\V1\Controllers\ListUsersController;
use Interface\Identity\Api\V1\Controllers\LoginController;
use Interface\Identity\Api\V1\Controllers\LogoutController;
use Interface\Identity\Api\V1\Controllers\RegisterController;
use Interface\Identity\Api\V1\Controllers\UpdatePasswordController;
use Interface\Identity\Api\V1\Controllers\UpdateUserController;

Route::post('register', RegisterController::class)
    ->middleware('throttle:5,1')
    ->withoutMiddleware('auth:sanctum')
    ->name('register');

Route::post('login', LoginController::class)
    ->middleware('throttle:5,1')
    ->withoutMiddleware('auth:sanctum')
    ->name('login');

Route::post('logout', LogoutController::class)
    ->name('logout');

Route::put('password', UpdatePasswordController::class)
    ->name('password.update');

Route::prefix('users')
    ->name('users.')
    ->group(function (): void {
        Route::get('/', ListUsersController::class)
            ->name('index');

        Route::post('/', CreateUserController::class)
            ->name('store');

        Route::put('{user}', UpdateUserController::class)
            ->name('update');

        Route::delete('{user}', DeleteUserController::class)
            ->name('destroy');
    });
