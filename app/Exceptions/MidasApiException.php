<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Support\Utils\ApiErrorResponse;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

abstract class MidasApiException extends Exception
{
    public function __construct(
        string $message,
        protected readonly int $statusCode = Response::HTTP_BAD_REQUEST,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function render(): Response
    {
        return ApiErrorResponse::make($this->getMessage(), $this->statusCode);
    }
}
