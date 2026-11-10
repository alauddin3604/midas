<?php

declare(strict_types=1);

namespace App\Policies\Identity;

use App\Enums\Identity\Permission;
use App\Models\Identity\User;

final class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->role->grants(Permission::ManageUsers);
    }

    public function create(User $actor): bool
    {
        return $actor->role->grants(Permission::ManageUsers);
    }

    public function update(User $actor, User $target): bool
    {
        return $actor->role->grants(Permission::ManageUsers);
    }
}
