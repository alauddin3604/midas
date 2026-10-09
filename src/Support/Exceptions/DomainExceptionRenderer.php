<?php

declare(strict_types=1);

namespace Support\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Support\Bases\Exceptions\DomainException;
use Support\Contracts\MapsDomainExceptions;
use Throwable;

final class DomainExceptionRenderer
{
    /**
     * @param  iterable<MapsDomainExceptions>  $maps
     */
    public function __construct(private iterable $maps) {}

    public function render(Throwable $e, Request $request): ?JsonResponse
    {
        if (! $e instanceof DomainException || ! $request->expectsJson()) {
            return null;
        }

        foreach ($this->maps as $map) {
            foreach ($map->statusCodes() as $class => $status) {
                if ($e instanceof $class) {
                    return response()->json(['message' => $e->getMessage()], $status);
                }
            }
        }

        return null;
    }
}
