<?php

declare(strict_types=1);

namespace Domain\Ticketing\ValueObjects;

use DateTimeInterface;
use InvalidArgumentException;
use Stringable;

use function sprintf;

final readonly class TicketNumber implements Stringable
{
    private const string PREFIX = 'TIC';

    private const string PATTERN = '/^TIC-\d{8}-\d{6,}$/';

    private function __construct(
        private string $value,
    ) {}

    public static function generate(DateTimeInterface $date, int $sequence): self
    {
        if ($sequence < 1) {
            throw new InvalidArgumentException('Sequence must be 1 or more.');
        }

        return new self(sprintf(
            '%s-%s-%06d',
            self::PREFIX,
            $date->format('Ymd'),
            $sequence,
        ));
    }

    public static function fromString(string $value): self
    {
        if (preg_match(self::PATTERN, $value) !== 1) {
            throw new InvalidArgumentException("Invalid ticket number: {$value}");
        }

        return new self($value);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
