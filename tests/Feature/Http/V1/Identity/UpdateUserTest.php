<?php

declare(strict_types=1);

use App\Enums\Identity\Role;
use App\Events\Identity\UserUpdated;
use App\Models\Identity\User;
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

it('ignores updating email that is owned by the user', function (): void {
    $admin = createUser([
        'role' => Role::Admin,
    ]);

    $member = createUser();

    Sanctum::actingAs($admin);

    $response = putJson(route('v1.users.update', $member), [
        'name' => 'Test User',
        'email' => $member->email,
        'role' => Role::Member,
    ]);
    $response->assertNoContent();
});

it('cannot update user if it does not exist', function (): void {
    $admin = createUser([
        'role' => Role::Admin,
    ]);

    Sanctum::actingAs($admin);

    $response = putJson(route('v1.users.update', 999), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'role' => Role::Member->value,
    ]);

    $response
        ->assertNotFound()
        ->assertJson([
            'message' => 'The requested URL or resource does not exist.',
        ]);
});

it('cannot update user if it using other user email', function (): void {
    $admin = createUser([
        'role' => Role::Admin,
    ]);

    $member = createUser();

    Sanctum::actingAs($admin);

    $response = putJson(route('v1.users.update', $member->id), [
        'name' => 'Test User',
        'email' => $admin->email,
        'role' => Role::Member->value,
    ]);

    $response
        ->assertUnprocessable()
        ->assertInvalid([
            'email' => 'The email has already been taken.',
        ]);
});
