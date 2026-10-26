<?php

declare(strict_types=1);

use Domain\Identity\Enums\Role;
use Domain\Identity\Payloads\RegisterUserPayload;

it('can convert register user payload to array', function (): void {
    $payload = new RegisterUserPayload(
        name: 'Joe Doe',
        email: 'joe@example.com',
        password: 'password',
        role: Role::Member,
    );

    expect($payload->toArray())
        ->toEqual([
            'name' => 'Joe Doe',
            'email' => 'joe@example.com',
            'password' => 'password',
            'role' => Role::Member,
        ]);
});
