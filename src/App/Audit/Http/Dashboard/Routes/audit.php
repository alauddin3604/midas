<?php

declare(strict_types=1);

use App\Audit\Http\Dashboard\Livewire\AuditLogPage;
use Domain\Identity\Enums\Permission;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth',
    Authorize::using(Permission::AccessDashboard),
    Authorize::using(Permission::ViewAuditLog),
])->group(function (): void {
    Route::livewire('audit-log', AuditLogPage::class)->name('audit.index');
});
