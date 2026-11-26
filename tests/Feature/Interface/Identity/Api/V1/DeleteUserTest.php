<?php

declare(strict_types=1);

use function Pest\Laravel\assertModelMissing;
use function Pest\Laravel\deleteJson;

it('throws exception when deleting own ID', function (): void {
    $admin = actingAsAdmin();

    $response = deleteJson(route('v1.users.destroy', $admin));

    $response
        ->assertConflict()
        ->assertJson([
            'message' => 'You cannot delete your own account.',
        ]);
});

it('can delete user', function (): void {
    actingAsAdmin();
    $user = createUser();

    $response = deleteJson(route('v1.users.destroy', $user));

    $response->assertNoContent();

    assertModelMissing($user);
});

it('cannot delete user when user does not exists', function (): void {
    actingAsAdmin();

    $response = deleteJson(route('v1.users.destroy', 99999));
    $response->assertNotFound();
    $response->assertJson([
        'message' => 'The requested URL or resource does not exist.',
    ]);
});
