<?php

namespace Interface\Shared\Api\Exceptions;

use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

abstract class ApiExceptionMapper
{
    /** @return array<class-string<Throwable>, int> */
    abstract protected function statusMap(): array;

    final public function __invoke(Exceptions $exceptions): void
    {
        $exceptions->render(
            function (Throwable $e, Request $request): ?JsonResponse {
                $status = $this->statusMap()[$e::class] ?? null;

                if ($status === null || ! $request->expectsJson()) {
                    return null;
                }

                return new JsonResponse(['message' => $e->getMessage()], $status);
            }
        );
    }
}
