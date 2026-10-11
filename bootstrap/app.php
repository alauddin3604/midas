<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Support\Bases\Exceptions\DomainException;
use Support\Exceptions\ApiExceptionRenderer;
use Support\Exceptions\DomainExceptionRenderer;
use Support\Utils\DomainRouteLoader;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        health: '/up',
        then: DomainRouteLoader::load(...),
    )
    ->withCommands(glob(dirname(__DIR__).'/src/App/*/Console', GLOB_ONLYDIR) ?: [])
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (DomainException $e, Request $request): ?JsonResponse {
            return app(DomainExceptionRenderer::class)->render($e, $request);
        });

        $exceptions->render(function (Throwable $e, Request $request): ?JsonResponse {
            return app(ApiExceptionRenderer::class)->render($e, $request);
        });
    })->create();

$app->useAppPath($app->basePath('src/App'));

return $app;
