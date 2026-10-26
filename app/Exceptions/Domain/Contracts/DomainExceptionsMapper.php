<?php

declare(strict_types=1);

namespace App\Exceptions\Domain\Contracts;

use DomainException;

interface DomainExceptionsMapper
{
    /**
     * @return array<class-string<DomainException>, int>
     */
    public function map(): array;
}
