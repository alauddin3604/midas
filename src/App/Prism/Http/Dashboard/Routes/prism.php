<?php

declare(strict_types=1);

use App\Prism\Http\Dashboard\Livewire\DashboardPage;
use Domain\Identity\Enums\Permission;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard')->name('home');

Route::middleware([
    'auth',
    Authorize::using(Permission::AccessDashboard),
])->group(function (): void {
    Route::livewire('dashboard', DashboardPage::class)->name('dashboard');
});
