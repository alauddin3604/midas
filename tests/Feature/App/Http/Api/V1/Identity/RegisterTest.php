<?php

use Domain\Identity\Enums\Role;
use Domain\Identity\Models\User;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\postJson;

it('registers a member', function (): void {
    postJson(route('v1.register'), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'secret-password',
    ])
        ->assertCreated()
        ->assertJsonPath('data.email', 'jane@example.com')
        ->assertJsonPath('data.role', 'member')
        ->assertJsonMissingPath('data.password');

    assertDatabaseHas('users', ['email' => 'jane@example.com']);
});

it('ignores a role sent by the client', function (): void {
    postJson(route('v1.register'), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'secret-password',
        'role' => 'admin',
    ])->assertCreated();

    expect(User::query()->firstOrFail()->role)->toBe(Role::Member);
});

it('answers 409 for an email that is already registered', function (): void {
    User::factory()->create(['email' => 'jane@example.com']);

    postJson(route('v1.register'), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'secret-password',
    ])
        ->assertConflict()
        ->assertJson([
            'message' => 'A user with email [jane@example.com] is already registered.',
        ]);
});

it('validates the payload', function (): void {
    postJson(route('v1.register'), [])
        ->assertUnprocessable()
        ->assertJsonPath('errors.*.field', ['name', 'email', 'password']);
});
