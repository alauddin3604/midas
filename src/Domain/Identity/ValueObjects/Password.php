<?php

declare(strict_types=1);

namespace Domain\Identity\ValueObjects;

use Illuminate\Support\Str;
use InvalidArgumentException;

final readonly class Password
{
    /**
     * Creates a new Password value object.
     *
     * @param  string  $value  The password value.
     */
    protected function __construct(public string $value)
    {
        if (empty($this->value)) {
            throw new InvalidArgumentException('Password cannot be empty.');
        }

        if (Str::length($this->value) < 8) {
            throw new InvalidArgumentException('Password must be at least 8 characters long.');
        }

        if (! preg_match('/[A-Z]/', $this->value)) {
            throw new InvalidArgumentException('Password must contain at least one uppercase letter.');
        }

        if (! preg_match('/[a-z]/', $this->value)) {
            throw new InvalidArgumentException('Password must contain at least one lowercase letter.');
        }

        if (! preg_match('/[0-9]/', $this->value)) {
            throw new InvalidArgumentException('Password must contain at least one digit.');
        }
    }

    /**
     * Creates a Password value object from a string.
     */
    public static function fromString(string $value): self
    {
        return new self($value);
    }
}
