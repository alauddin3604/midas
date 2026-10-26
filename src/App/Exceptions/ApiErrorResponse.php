<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ApiErrorResponse
{
    /**
     * Build the shared error envelope: a message plus a list of field-level errors.
     *
     * @param  list<array{field: string, error: string}>  $errors
     * @param  array<string, string>  $headers
     */
    public static function make(string $message, int $status, array $errors = [], array $headers = []): JsonResponse
    {
        return new JsonResponse(
            [
                'message' => $message,
                'errors' => $errors,
            ],
            $status,
            $headers,
        );
    }

    /**
     * Determine whether the caller should receive a JSON error instead of a redirect or HTML page.
     */
    public static function isExpectedBy(Request $request): bool
    {
        return $request->is('api/*') || $request->expectsJson();
    }
}
