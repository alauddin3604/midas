<?php

declare(strict_types=1);

use Domain\Identity\Enums\Role;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\postJson;

it('cannot create a user if unauthorized', function (): void {
    $member = createUser([
        'role' => Role::Member,
    ]);

    Sanctum::actingAs($member);

    $response = postJson(route('v1.users.store'), [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'Password#123',
        'role' => 'member',
    ]);

    $response->assertForbidden();

    $response->assertJson([
        'message' => 'This action is unauthorized.',
    ]);
});

it('can create a user', function (): void {
    $admin = createUser([
        'role' => Role::Admin,
    ]);

    Sanctum::actingAs($admin);

    $response = postJson(route('v1.users.store'), [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'Password#123',
        'role' => 'member',
    ]);

    $response->assertCreated();

    $response->assertJson([
        'data' => [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'role' => 'Member',
        ],
    ]);
});
