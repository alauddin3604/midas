<?php

declare(strict_types=1);

use Database\Factories\UserFactory;
use Domain\Identity\Actions\DeleteUserFromEmailAction;
use Domain\Identity\Exceptions\UserNotFound;

use function Pest\Laravel\assertModelExists;
use function Pest\Laravel\assertModelMissing;

it('can delete a user from email', function (): void {
    $user = UserFactory::new()->create();

    assertModelExists($user);

    $action = app(DeleteUserFromEmailAction::class);
    $action->execute($user->email);

    assertModelMissing($user);
});

it('cannot delete user from non exist email', function (): void {
    $action = app(DeleteUserFromEmailAction::class);
    $action->execute('joe@example.com');
})->throws(
    UserNotFound::class,
    'User with email [joe@example.com] does not exist.',
);
