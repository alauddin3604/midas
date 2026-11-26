<?php

use Database\Factories\UserFactory;
use Domain\Identity\Enums\Role;
use Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()
    ->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

function createUser(array $attributes = []): User
{
    return UserFactory::new()->create($attributes);
}

function actingAsAdmin(): User
{
    $user = createUser([
        'role' => Role::Admin,
    ]);

    Sanctum::actingAs($user);

    return $user;
}

/**
 * Assert the JSON error.
 */
function assertProblemJson(
    TestResponse $response,
    int $status,
    string $message,
): TestResponse {
    return $response
        ->assertStatus($status)
        ->assertHeader('Content-Type', 'application/problem+json')
        ->assertJsonStructure(['message'])
        ->assertJsonFragment([
            'message' => $message,
        ]);
}
