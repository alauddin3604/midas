<?php

use Domain\Identity\Models\User;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\postJson;
use function Pest\Laravel\withToken;

it('revokes only the token used for the request', function () {
    $user = User::factory()->create();
    $current = $user->createToken('phone')->plainTextToken;
    $other = $user->createToken('laptop');

    withToken($current)->postJson(route('api.v1.logout'))->assertNoContent();

    assertDatabaseCount('personal_access_tokens', 1);
    expect($user->tokens()->first()->is($other->accessToken))->toBeTrue();
});

it('rejects the token after it has been revoked', function () {
    $token = User::factory()->create()->createToken('phone')->plainTextToken;

    withToken($token)->postJson(route('api.v1.logout'))->assertNoContent();

    app('auth')->forgetGuards();

    withToken($token)->postJson(route('api.v1.logout'))->assertUnauthorized();
});

it('returns 401 when no token is provided', function () {
    postJson(route('api.v1.logout'))
        ->assertUnauthorized()
        ->assertExactJson(['message' => 'Unauthenticated.', 'errors' => []]);
});
