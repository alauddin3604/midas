<?php

declare(strict_types=1);

use Database\Factories\UserFactory;
use Domain\Identity\Actions\DeleteUserFromEmailAction;

use function Pest\Laravel\artisan;
use function Pest\Laravel\assertModelMissing;

it('fails when the email argument is not a string', function () {
    artisan('identity:delete-user', ['email' => ['a@example.com', 'b@example.com']])
        ->expectsOutputToContain('Invalid email. Please enter a valid email address.')
        ->assertFailed();
});

it('cannot delete user with invalid email', function (): void {
    $email = 'joe-doe';

    $console = artisan('identity:delete-user', [
        'email' => $email,
    ]);

    $console
        ->assertFailed()
        ->expectsOutput('Invalid email. Please enter a valid email address.');
});

it('cannot delete user with non exist email', function (): void {
    $email = 'joe@example.com';

    artisan('identity:delete-user', ['email' => $email])
        ->assertFailed()
        ->expectsOutput("User with email [{$email}] does not exist. Please check the email.")
        ->run();
});

it('can delete user with email', function (): void {
    $user = UserFactory::new()->create();
    $email = $user->email;

    artisan('identity:delete-user', ['email' => $email])
        ->assertSuccessful()
        ->expectsOutput("User with email [{$email}] is successfully deleted.")
        ->run();

    assertModelMissing($user);
});
