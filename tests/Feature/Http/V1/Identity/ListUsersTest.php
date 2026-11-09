<?php

declare(strict_types=1);

use Domain\Identity\Enums\Role;
use Domain\Identity\Models\User;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\getJson;

it('cannot list users if unauthenticated', function (): void {
    $response = getJson(route('v1.users.index'));
    $response->assertUnauthorized();
    $response->assertJson([
        'message' => 'Unauthenticated.',
        'errors' => [],
    ]);
});

it('cannot list users if unauthorized', function (): void {
    Sanctum::actingAs(User::factory()->create());

    $response = getJson(route('v1.users.index'));
    $response->assertForbidden();
    $response->assertJson([
        'message' => 'This action is unauthorized.',
        'errors' => [],
    ]);
});

it('can list users', function (): void {
    $user = User::factory()->create([
        'role' => Role::Admin,
    ]);

    Sanctum::actingAs($user);

    $response = getJson(route('v1.users.index'));
    $response->assertOk();
    $response->assertJsonCount(1, 'data');
    $response->assertJson([
        'data' => [
            [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => 'Admin',
            ],
        ],
    ]);
});
