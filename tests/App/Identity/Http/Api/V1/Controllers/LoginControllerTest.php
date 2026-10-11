<?php

use Domain\Identity\Models\User;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\postJson;
use function Pest\Laravel\travel;
use function Pest\Laravel\withToken;

it('logs a user in with valid credentials', function () {
    $user = User::factory()->create(['email' => 'jane@example.com']);

    $response = postJson('/api/v1/login', ['email' => 'jane@example.com', 'password' => 'password'])
        ->assertOk()
        ->assertJsonStructure(['data' => ['token']]);

    expect($user->tokens)->toHaveCount(1);

    withToken($response->json('data.token'))
        ->postJson('/api/v1/logout')
        ->assertNoContent();
});

it('rejects a wrong password', function () {
    User::factory()->create(['email' => 'jane@example.com']);

    postJson('/api/v1/login', ['email' => 'jane@example.com', 'password' => 'wrong'])
        ->assertUnprocessable()
        ->assertExactJson([
            'message' => 'Validation failed.',
            'errors' => [['field' => 'email', 'error' => 'The provided credentials are incorrect.']],
        ]);

    assertDatabaseCount('personal_access_tokens', 0);
});

it('rejects an unknown email', function () {
    postJson('/api/v1/login', ['email' => 'nobody@example.com', 'password' => 'password'])
        ->assertUnprocessable()
        ->assertJsonPath('errors.0.field', 'email');
});

it('validates the payload', function () {
    postJson('/api/v1/login', [])
        ->assertUnprocessable()
        ->assertJsonPath('errors.*.field', ['email', 'password']);
});

it('throttles repeated attempts', function () {
    foreach (range(1, 5) as $ignored) {
        postJson('/api/v1/login', ['email' => 'a@example.com', 'password' => $ignored]);
    }

    postJson('/api/v1/login', ['email' => 'a@example.com', 'password' => 'x'])
        ->assertTooManyRequests();
});

it('issues tokens that stop working after the configured expiration', function () {
    config(['sanctum.expiration' => 60]);
    User::factory()->create(['email' => 'jane@example.com']);

    $token = postJson('/api/v1/login', ['email' => 'jane@example.com', 'password' => 'password'])
        ->json('data.token');

    travel(61)->minutes();

    withToken($token)->postJson('/api/v1/logout')->assertUnauthorized();
});
