<?php

declare(strict_types=1);

namespace Domain\Ticketing\Casts;

use Domain\Ticketing\ValueObjects\TicketNumber;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/** @implements CastsAttributes<TicketNumber, TicketNumber|string> */
final class TicketNumberCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?TicketNumber
    {
        return $value === null ? null : TicketNumber::fromString($value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return match (true) {
            $value === null => null,
            $value instanceof TicketNumber => $value->value(),
            is_string($value) => TicketNumber::fromString($value)->value(),
            default => throw new InvalidArgumentException('Invalid ticket number value.'),
        };
    }
}
