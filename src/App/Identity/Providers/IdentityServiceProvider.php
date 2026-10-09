<?php

declare(strict_types=1);

namespace App\Identity\Providers;

use App\Identity\Http\IdentityExceptionMap;
use Domain\Identity\Enums\Permission;
use Domain\Identity\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Support\Contracts\MapsDomainExceptions;

final class IdentityServiceProvider extends ServiceProvider
{
    /**
     * Register the Identity domain's exception map with the domain exception renderer.
     */
    public function register(): void
    {
        $this->app->tag([IdentityExceptionMap::class], MapsDomainExceptions::TAG);
    }

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
