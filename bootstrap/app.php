<?php

declare(strict_types=1);

use App\Exceptions\Domain\DomainExceptionsRegistry;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Support\Utils\ApiErrorResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        health: '/up',
        then: function (): void {
            $versions = glob(base_path('routes/api/*'), GLOB_ONLYDIR) ?: [];
            sort($versions);

            foreach ($versions as $dir) {
                $version = basename($dir);

                $files = glob("{$dir}/*.php") ?: [];
                sort($files);

                foreach ($files as $file) {
                    Route::middleware('api')
                        ->prefix("api/{$version}")
                        ->name("api.{$version}.")
                        ->group($file);
                }
            }
        },
    )
    ->withCommands([
        __DIR__.'/../src/App/Console/Commands',
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn (): null => null);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(fn (): bool => true);

        $exceptions->dontReport(DomainException::class);

        $exceptions->render(function (DomainException $e, Request $request): JsonResponse {
            $status = resolve(DomainExceptionsRegistry::class)->map()[$e::class]
                ?? Response::HTTP_BAD_REQUEST;

            return ApiErrorResponse::make($e->getMessage(), $status);
        });

        $exceptions->render(function (ValidationException $e): JsonResponse {
            $errors = [];

            foreach ($e->errors() as $field => $messages) {
                $errors[] = ['field' => (string) $field, 'error' => $messages[0]];
            }

            return ApiErrorResponse::make(
                message: 'Validation failed.',
                status: $e->status,
                errors: $errors,
            );
        });

        $exceptions->render(fn (AuthenticationException $e): JsonResponse => ApiErrorResponse::make(
            'Unauthenticated.',
            Response::HTTP_UNAUTHORIZED,
        ));

        $exceptions->render(function (HttpExceptionInterface $e): ?JsonResponse {
            if ($e->getStatusCode() >= Response::HTTP_INTERNAL_SERVER_ERROR) {
                return null;
            }

            $message = match (true) {
                $e instanceof NotFoundHttpException => 'Not found.',
                $e->getMessage() !== '' => $e->getMessage(),
                isset(Response::$statusTexts[$e->getStatusCode()]) => Response::$statusTexts[$e->getStatusCode()],
                default => 'Error.',
            };

            return ApiErrorResponse::make(
                message: $message,
                status: $e->getStatusCode(),
                headers: $e->getHeaders(),
            );
        });

        $exceptions->render(function (Throwable $e, Request $request): ?JsonResponse {
            if (config('app.debug')) {
                return null;
            }

            return ApiErrorResponse::make(
                'Unexpected exception occured.',
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        });
    })->create();
