<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorLaravel\Rector\Class_\RemoveDefaultAuthorizeTrueFromFormRequestRector;
use RectorLaravel\Rector\FuncCall\ConfigToTypedConfigMethodCallRector;
use RectorLaravel\Rector\MethodCall\ResponseHelperCallToJsonResponseRector;
use RectorLaravel\Rector\StaticCall\CarbonToDateFacadeRector;
use RectorLaravel\Rector\StaticCall\StaticFacadeToServiceMethodCallRector;
use RectorLaravel\Set\LaravelSetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/app',
        __DIR__.'/bootstrap/app.php',
        __DIR__.'/tests',
    ])
    ->withSkip([
        __DIR__.'/bootstrap/cache',
        __DIR__.'/storage',
        __DIR__.'/vendor',
    ])
    ->withComposerBased(
        laravel: true,
    )
    ->withSets([
        LaravelSetList::LARAVEL_COLLECTION,
        LaravelSetList::LARAVEL_ELOQUENT_MAGIC_METHOD_TO_QUERY_BUILDER,
        // LaravelSetList::LARAVEL_FACTORIES,
    ])
    ->withRules([
        CarbonToDateFacadeRector::class,
        ConfigToTypedConfigMethodCallRector::class,
        RemoveDefaultAuthorizeTrueFromFormRequestRector::class,
        ResponseHelperCallToJsonResponseRector::class,
        StaticFacadeToServiceMethodCallRector::class,
    ]);
