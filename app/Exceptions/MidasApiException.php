<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Support\Utils\ApiErrorResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

abstract class MidasApiException extends Exception
{
    /**
     * The HTTP status code.
     */
    private int $statusCode;

    public function __construct(
        string $message,
        int $statusCode = Response::HTTP_BAD_REQUEST,
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);

        $this->statusCode = $statusCode;
    }

    public function render(): Response
    {
        return ApiErrorResponse::make($this->getMessage(), $this->statusCode);
    }
}
