<?php

declare(strict_types=1);

use App\Console\DomainCommandDiscovery;
use App\Exceptions\ApiExceptionRenderer;
use App\Exceptions\DomainExceptionHandler;
use App\Routing\DomainRouteLoader;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        health: '/up',
        then: DomainRouteLoader::load(...),
    )
    ->withCommands(DomainCommandDiscovery::classes())
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(app(DomainExceptionHandler::class));

        $exceptions->render(function (Throwable $e, Request $request): ?JsonResponse {
            return app(ApiExceptionRenderer::class)->render($e, $request);
        });
    })->create();

$app->useAppPath($app->basePath('src/App'));

return $app;
