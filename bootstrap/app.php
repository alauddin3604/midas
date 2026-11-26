<?php

declare(strict_types=1);

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Interface\Identity\Api\Exceptions\IdentityExceptionMapper;
use Interface\Identity\Console\DeleteUserCommand;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        apiPrefix: '',
        health: '/up',
        then: function (): void {
            foreach (glob(base_path('src/Interface/*/Api/V1/routes.php')) ?: [] as $file) {
                Route::middleware(['api', 'auth:sanctum'])
                    ->prefix('v1')
                    ->name('v1.')
                    ->group($file);
            }
        },
    )
    ->withCommands([
        DeleteUserCommand::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn (): null => null);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(fn (): bool => true);

        $exceptions->dontReport([
            DomainException::class,
        ]);

        $mappers = [
            IdentityExceptionMapper::class,
        ];

        foreach ($mappers as $mapper) {
            (new $mapper)($exceptions);
        }

        $exceptions->render(function (ValidationException $e): JsonResponse {
            return new JsonResponse([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], $e->status);
        });

        $exceptions->render(fn (AuthenticationException $e): JsonResponse => new JsonResponse(
            ['message' => 'Unauthenticated.'],
            Response::HTTP_UNAUTHORIZED,
        ));

        $exceptions->render(function (HttpExceptionInterface $e): ?JsonResponse {
            if ($e->getStatusCode() >= Response::HTTP_INTERNAL_SERVER_ERROR) {
                return null;
            }

            $message = match (true) {
                $e instanceof NotFoundHttpException => 'The requested URL or resource does not exist.',
                $e->getMessage() !== '' => $e->getMessage(),
                isset(Response::$statusTexts[$e->getStatusCode()]) => Response::$statusTexts[$e->getStatusCode()],
                default => 'Error.',
            };

            return new JsonResponse(
                data: ['message' => $message],
                status: $e->getStatusCode(),
                headers: $e->getHeaders(),
            );
        });

        $exceptions->render(function (Throwable $e, Request $request): ?JsonResponse {
            if (config()->boolean('app.debug')) {
                return null;
            }

            return new JsonResponse([
                'message' => 'Unexpected exception occured.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        });
    })->create();

return $app->useAppPath(__DIR__.'../src/Application');
