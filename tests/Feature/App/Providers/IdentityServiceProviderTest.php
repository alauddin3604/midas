<?php

use Domain\Identity\Enums\Permission;
use Domain\Identity\Models\User;
use Illuminate\Support\Facades\Gate;

it('exposes domain permissions as gate abilities', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->create();

    expect(Gate::forUser($admin)->allows(Permission::ManageUsers))->toBeTrue()
        ->and(Gate::forUser($member)->allows(Permission::ManageUsers))->toBeFalse();
});

it('defines a gate for every permission', function () {
    foreach (Permission::cases() as $permission) {
        expect(Gate::has($permission))->toBeTrue();
    }
});
