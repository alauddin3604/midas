<?php

declare(strict_types=1);

namespace App\Policies\Identity;

use Domain\Identity\Enums\Permission;
use Domain\Identity\Models\User;

class UserPolicy
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
