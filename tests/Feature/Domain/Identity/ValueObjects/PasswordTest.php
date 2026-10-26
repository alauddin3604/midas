<?php

declare(strict_types=1);

use Domain\Identity\ValueObjects\Password;

it('throws an exception when password is empty', function (): void {
    Password::fromString('');
})->throws(InvalidArgumentException::class, 'Password cannot be empty.');

it('throws an exception when password is less than 8 characters', function (): void {
    Password::fromString('short');
})->throws(InvalidArgumentException::class, 'Password must be at least 8 characters long.');

it('throws an exception when password does not contain an uppercase letter', function (): void {
    Password::fromString('lowercase1');
})->throws(InvalidArgumentException::class, 'Password must contain at least one uppercase letter.');

it('throws an exception when password does not contain a lowercase letter', function (): void {
    Password::fromString('UPPERCASE1');
})->throws(InvalidArgumentException::class, 'Password must contain at least one lowercase letter.');

it('throws an exception when password does not contain a digit', function (): void {
    Password::fromString('NoDigitsHere');
})->throws(InvalidArgumentException::class, 'Password must contain at least one digit.');

it('can create a valid password', function (): void {
    $password = Password::fromString('ValidPass1');

    expect($password->value)->toBe('ValidPass1');
});
