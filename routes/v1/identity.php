<?php

declare(strict_types=1);

use App\Http\Controllers\V1\Identity\CreateUserController;
use App\Http\Controllers\V1\Identity\DeleteUserController;
use App\Http\Controllers\V1\Identity\LoginController;
use App\Http\Controllers\V1\Identity\RegisterController;
use App\Http\Controllers\V1\Identity\UpdatePasswordController;
use App\Http\Controllers\V1\Identity\UpdateUserController;
use App\Http\V1\Controllers\Identity\ListUsersController;
use App\Http\V1\Controllers\Identity\LogoutController;
use Illuminate\Support\Facades\Route;

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
