<?php

declare(strict_types=1);

namespace App\Providers;

use Domain\Identity\Enums\Permission;
use Domain\Identity\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

final class IdentityServiceProvider extends ServiceProvider
{
    /**
     * Expose every Identity permission as a Gate ability, e.g. `Permission::ManageUsers`.
     */
    public function boot(): void
    {
        foreach (Permission::cases() as $permission) {
            Gate::define($permission, fn (User $user): bool => $user->role->grants($permission));
        }
    }
}
