<?php

use Domain\Identity\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\postJson;

it('logs a user out', function () {
    $user = User::factory()->create();

    actingAs($user)->postJson('/api/v1/logout')->assertNoContent();

    assertGuest();
});

it('requires authentication to log out', function () {
    postJson('/api/v1/logout')->assertUnauthorized();
});
