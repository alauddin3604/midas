<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Exceptions\Domain\DomainExceptionsRegistry;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final readonly class DomainExceptionHandler
{
    public function __construct(private DomainExceptionsRegistry $scanner) {}

    public function __invoke(DomainException $e, Request $request): ?JsonResponse
    {
        if (! ApiErrorResponse::isExpectedBy($request)) {
            return null;
        }

        $status = $this->scanner->map()[$e::class] ?? null;

        return $status === null
            ? null
            : ApiErrorResponse::make($e->getMessage(), $status);
    }
}
