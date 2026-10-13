<?php

declare(strict_types=1);

use Database\Factories\UserFactory;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertGuest;

it('can logout a user', function (): void {
    $user = UserFactory::new()->create();

    actingAs($user)
        ->post('logout')
        ->assertRedirectToRoute('login');

    assertGuest('web');
});
