<?php

use Domain\Identity\Models\User;

use function Pest\Laravel\assertAuthenticatedAs;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\postJson;

it('logs a user in with valid credentials', function () {
    $user = User::factory()->create(['email' => 'jane@example.com']);

    postJson('/api/v1/login', ['email' => 'jane@example.com', 'password' => 'password'])
        ->assertOk()
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.email', 'jane@example.com')
        ->assertJsonMissingPath('data.password');

    assertAuthenticatedAs($user);
});

it('rejects a wrong password', function () {
    User::factory()->create(['email' => 'jane@example.com']);

    postJson('/api/v1/login', ['email' => 'jane@example.com', 'password' => 'wrong'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');

    assertGuest();
});

it('rejects an unknown email', function () {
    postJson('/api/v1/login', ['email' => 'nobody@example.com', 'password' => 'password'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');
});

it('validates the payload', function () {
    postJson('/api/v1/login', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email', 'password']);
});

it('throttles repeated attempts', function () {
    foreach (range(1, 5) as $ignored) {
        postJson('/api/v1/login', ['email' => 'a@example.com', 'password' => $ignored]);
    }

    postJson('/api/v1/login', ['email' => 'a@example.com', 'password' => 'x'])
        ->assertTooManyRequests();
});
