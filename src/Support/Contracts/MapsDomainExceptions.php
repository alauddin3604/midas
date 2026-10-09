<?php

declare(strict_types=1);

namespace Support\Contracts;

use Throwable;

interface MapsDomainExceptions
{
    /**
     * The container tag under which every domain registers its map.
     */
    public const string TAG = 'domain.exception-maps';

    /**
     * @return array<class-string<Throwable>, int>
     */
    public function statusCodes(): array;
}
