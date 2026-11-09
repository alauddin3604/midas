<?php

declare(strict_types=1);

use Domain\Identity\Enums\Role;
use Domain\Identity\Events\UserUpdated;
use Domain\Identity\Models\User;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\putJson;

it('can update a user', function (): void {
    $admin = createUser([
        'role' => Role::Admin,
    ]);

    $member = createUser();

    Sanctum::actingAs($admin);

    $response = putJson(route('v1.users.update', $member->id), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'role' => Role::Member->value,
    ]);
    $response->assertNoContent();

    assertDatabaseHas(User::class, [
        'id' => $member->id,
        'name' => 'Test User',
        'email' => 'test@example.com',
        'role' => Role::Member->value,
    ]);
});

it('dispatch an event after updating a user', function (): void {
    Event::fake();

    $admin = createUser([
        'role' => Role::Admin,
    ]);

    $member = createUser();

    Sanctum::actingAs($admin);

    $response = putJson(route('v1.users.update', $member->id), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'role' => Role::Member->value,
    ]);
    $response->assertNoContent();

    Event::assertDispatched(UserUpdated::class);
});
