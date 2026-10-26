<?php

use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\putJson;

it('cannot update the password if user is not authenticated', function (): void {
    $response = putJson(route('api.v1.password.update'), [
        'password' => 'NewPassword123!',
    ]);

    $response->assertUnauthorized();
});

it('rejects invalid passwords', function (mixed $password, mixed $confirmation, string $error): void {
    Sanctum::actingAs(createUser());

    putJson(route('api.v1.password.update'), ['password' => $password, 'password_confirmation' => $confirmation])
        ->assertUnprocessable()
        ->assertJson([
            'message' => 'Validation failed.',
            'errors' => [['field' => 'password', 'error' => $error]],
        ]);
})->with([
    'empty' => ['', '', 'The password field is required.'],
    'array' => [['NewPassword123!'], 'NewPassword123!', 'The password field must be a string.'],
    'not match' => ['NewPassword123!', 'DifferentPassword123!', 'The password field confirmation does not match.'],
    'too short' => ['NewPa1!', 'NewPa1!', 'The password field must be at least 8 characters.'],
    'no uppercase' => ['newpassword123!', 'newpassword123!', 'The password field must contain at least one uppercase and one lowercase letter.'],
    'no lowercase' => ['NEWPASSWORD123!', 'NEWPASSWORD123!', 'The password field must contain at least one uppercase and one lowercase letter.'],
]);

it('can update the password', function (): void {
    $user = createUser([
        'password' => 'OldPassword123!',
    ]);

    Sanctum::actingAs($user);

    $response = putJson(route('api.v1.password.update'), [
        'password' => 'NewPassword123!',
        'password_confirmation' => 'NewPassword123!',
    ]);

    $response->assertNoContent();

    $user->refresh();

    expect(Hash::check('NewPassword123!', $user->password))->toBeTrue()
        ->and(Hash::check('OldPassword123!', $user->password))->toBeFalse();
});
