<?php

declare(strict_types=1);

use App\Http\Dashboard\Controllers\Identity\LogoutController;
use App\Livewire\Identity\CreateUserPage;
use App\Livewire\Identity\EditUserPage;
use App\Livewire\Identity\ForgotPasswordPage;
use App\Livewire\Identity\LoginPage;
use App\Livewire\Identity\ResetPasswordPage;
use App\Livewire\Identity\UserIndexPage;
use Domain\Identity\Enums\Permission;
use Domain\Identity\Models\User;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::livewire('login', LoginPage::class)->name('login');
    Route::livewire('forgot-password', ForgotPasswordPage::class)->name('password.request');
    Route::livewire('reset-password/{token}', ResetPasswordPage::class)->name('password.reset');
});

Route::middleware(['auth', Authorize::using(Permission::AccessDashboard)])->group(function () {
    Route::post('logout', LogoutController::class)->name('logout');

    Route::prefix('users')->name('users.')->group(function () {
        Route::livewire('/', UserIndexPage::class)->name('index')->can('viewAny', User::class);
        Route::livewire('create', CreateUserPage::class)->name('create')->middleware('can:create,'.User::class);
        Route::livewire('{user}/edit', EditUserPage::class)->name('edit')->can('update', 'user');
    });
});
