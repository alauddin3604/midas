<?php

declare(strict_types=1);

use Domain\Identity\Enums\Permission;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Support\Facades\Route;
use Interfaces\Identity\Http\Dashboard\Controllers\LogoutController;
use Interfaces\Identity\Http\Dashboard\Livewire\CreateUserPage;
use Interfaces\Identity\Http\Dashboard\Livewire\EditUserPage;
use Interfaces\Identity\Http\Dashboard\Livewire\ForgotPasswordPage;
use Interfaces\Identity\Http\Dashboard\Livewire\LoginPage;
use Interfaces\Identity\Http\Dashboard\Livewire\ResetPasswordPage;
use Interfaces\Identity\Http\Dashboard\Livewire\UserIndexPage;

Route::middleware('guest')->group(function () {
    Route::livewire('login', LoginPage::class)->name('login');
    Route::livewire('forgot-password', ForgotPasswordPage::class)->name('password.request');
    Route::livewire('reset-password/{token}', ResetPasswordPage::class)->name('password.reset');
});

Route::middleware(['auth', Authorize::using(Permission::AccessDashboard)])->group(function () {
    Route::post('logout', LogoutController::class)->name('logout');

    Route::middleware(Authorize::using(Permission::ManageUsers))->prefix('users')->name('users.')->group(function () {
        Route::livewire('/', UserIndexPage::class)->name('index');
        Route::livewire('create', CreateUserPage::class)->name('create');
        Route::livewire('{user}/edit', EditUserPage::class)->name('edit');
    });
});
