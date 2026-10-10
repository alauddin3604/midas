<?php

use Domain\Identity\Enums\Permission;
use Domain\Identity\Enums\Role;

it('grants every permission to an admin', function () {
    foreach (Permission::cases() as $permission) {
        expect(Role::Admin->grants($permission))->toBeTrue();
    }
});

it('limits a member to dashboard access', function () {
    expect(Role::Member->grants(Permission::AccessDashboard))->toBeTrue()
        ->and(Role::Member->grants(Permission::ManageUsers))->toBeFalse()
        ->and(Role::Member->grants(Permission::ViewAuditLog))->toBeFalse();
});

it('grants every permission to at least one role', function () {
    foreach (Permission::cases() as $permission) {
        expect(array_filter(Role::cases(), fn (Role $role) => $role->grants($permission)))->not->toBeEmpty();
    }
});
