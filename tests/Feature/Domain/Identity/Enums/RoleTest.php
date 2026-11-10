<?php

use App\Enums\Identity\Permission;
use App\Enums\Identity\Role;

it('grants every permission to an admin', function () {
    foreach (Permission::cases() as $permission) {
        expect(Role::Admin->grants($permission))->toBeTrue();
    }
});

it('grants every permission to at least one role', function () {
    foreach (Permission::cases() as $permission) {
        expect(array_filter(Role::cases(), fn (Role $role) => $role->grants($permission)))->not->toBeEmpty();
    }
});
