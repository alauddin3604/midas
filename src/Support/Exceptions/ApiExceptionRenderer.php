<?php

declare(strict_types=1);

namespace Support\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Support\Utils\ApiErrorResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

final class ApiExceptionRenderer
{
    /**
     * Render validation, authentication and framework HTTP exceptions with the shared error envelope.
     */
    public function render(Throwable $e, Request $request): ?JsonResponse
    {
        if (! ApiErrorResponse::isExpectedBy($request)) {
            return null;
        }

        if ($e instanceof ValidationException) {
            return ApiErrorResponse::make(
                message: 'Validation failed.',
                status: $e->status,
                errors: $this->firstErrorPerField($e),
            );
        }

        if ($e instanceof AuthenticationException) {
            return ApiErrorResponse::make('Unauthenticated.', Response::HTTP_UNAUTHORIZED);
        }

        if ($e instanceof HttpExceptionInterface && $e->getStatusCode() < Response::HTTP_INTERNAL_SERVER_ERROR) {
            return ApiErrorResponse::make(
                message: $this->messageFor($e),
                status: $e->getStatusCode(),
                headers: $e->getHeaders(),
            );
        }

        return null;
    }

    /**
     * Keep only the first message of every failing field.
     *
     * @return list<array{field: string, error: string}>
     */
    private function firstErrorPerField(ValidationException $e): array
    {
        $errors = [];

        foreach ($e->errors() as $field => $messages) {
            $errors[] = ['field' => (string) $field, 'error' => $messages[0]];
        }

        return $errors;
    }

    /**
     * Pick a message that never leaks internals such as model class names.
     */
    private function messageFor(HttpExceptionInterface $e): string
    {
        if ($e instanceof NotFoundHttpException) {
            return 'Not found.';
        }

        if ($e->getMessage() !== '') {
            return $e->getMessage();
        }

        return Response::$statusTexts[$e->getStatusCode()] ?? 'Error.';
    }
}
