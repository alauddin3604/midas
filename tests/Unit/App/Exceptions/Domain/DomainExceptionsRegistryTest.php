<?php

declare(strict_types=1);

use App\Exceptions\Domain\Contracts\DomainExceptionsMapper;
use App\Exceptions\Domain\DomainExceptionsRegistry;

it('merges every mapper and caches the result', function (): void {
    $first = new class implements DomainExceptionsMapper
    {
        public int $calls = 0;

        public function map(): array
        {
            $this->calls++;

            return [RuntimeException::class => 409];
        }
    };

    $second = new class implements DomainExceptionsMapper
    {
        public function map(): array
        {
            return [LogicException::class => 403];
        }
    };

    $registry = new DomainExceptionsRegistry([$first, $second]);

    $expected = [
        RuntimeException::class => 409,
        LogicException::class => 403,
    ];

    expect($registry->map())->toBe($expected)
        ->and($registry->map())->toBe($expected)
        ->and($first->calls)->toBe(1);
});
