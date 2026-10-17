<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Support\Bases\Exceptions\ConflictException;
use Support\Bases\Exceptions\DomainException;
use Support\Bases\Exceptions\ForbiddenException;
use Support\Bases\Exceptions\InternalServerErrorException;
use Support\Bases\Exceptions\NotFoundException;
use Support\Bases\Exceptions\UnauthorizedException;
use Support\Bases\Exceptions\UnprocessableException;
use Symfony\Component\HttpFoundation\Response;

final class DomainExceptionHandler
{
    /** @var array<class-string<DomainException>, int> */
    private const STATUS_BY_CATEGORY = [
        UnauthorizedException::class => Response::HTTP_UNAUTHORIZED,
        ForbiddenException::class => Response::HTTP_FORBIDDEN,
        NotFoundException::class => Response::HTTP_NOT_FOUND,
        ConflictException::class => Response::HTTP_CONFLICT,
        UnprocessableException::class => Response::HTTP_UNPROCESSABLE_ENTITY,
        InternalServerErrorException::class => Response::HTTP_INTERNAL_SERVER_ERROR,
    ];

    /**
     * Invoke the domain exception handler.
     */
    public function __invoke(DomainException $e, Request $request): ?JsonResponse
    {
        if (! ApiErrorResponse::isExpectedBy($request)) {
            return null;
        }

        foreach (self::STATUS_BY_CATEGORY as $category => $status) {
            if ($e instanceof $category) {
                return ApiErrorResponse::make($e->getMessage(), $status);
            }
        }

        return null;
    }
}
