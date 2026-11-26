<?php

use Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Laravel\Sanctum\Contracts\HasAbilities;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\postJson;
use function Pest\Laravel\withToken;

it('revokes only the token used for the request', function () {
    $user = User::factory()->createOne();
    $current = $user->createToken('phone')->plainTextToken;
    $other = $user->createToken('laptop');

    withToken($current)->postJson(route('v1.logout'))->assertNoContent();

    /** @var HasAbilities&Model $token */
    $token = $user->tokens()->firstOrFail();

    assertDatabaseCount('personal_access_tokens', 1);
    expect($token->is($other->accessToken))->toBeTrue();
});

it('rejects the token after it has been revoked', function () {
    $token = User::factory()->create()->createToken('phone')->plainTextToken;

    withToken($token)->postJson(route('v1.logout'))->assertNoContent();

    App::make('auth')->forgetGuards();

    withToken($token)->postJson(route('v1.logout'))->assertUnauthorized();
});

it('returns 401 when no token is provided', function () {
    postJson(route('v1.logout'))
        ->assertUnauthorized()
        ->assertExactJson(['message' => 'Unauthenticated.']);
});
