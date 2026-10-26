<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

use App\Exceptions\Domain\Contracts\DomainExceptionsMapper;

final class DomainExceptionsRegistry
{
    /** @var array<class-string, int>|null */
    private ?array $map = null;

    /**
     * @param  iterable<DomainExceptionsMapper>  $mappers
     */
    public function __construct(private readonly iterable $mappers) {}

    /**
     * @return array<class-string, int>
     */
    public function map(): array
    {
        if ($this->map !== null) {
            return $this->map;
        }

        $map = [];

        foreach ($this->mappers as $mapper) {
            $map = [...$map, ...$mapper->map()];
        }

        return $this->map = $map;
    }
}
