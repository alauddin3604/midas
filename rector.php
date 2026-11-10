<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorLaravel\Rector\FuncCall\ConfigToTypedConfigMethodCallRector;
use RectorLaravel\Rector\MethodCall\ResponseHelperCallToJsonResponseRector;
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
        __DIR__.'/database',
        __DIR__.'/storage',
        __DIR__.'/vendor',
        StaticFacadeToServiceMethodCallRector::class => [
            __DIR__.'/app/Providers',
        ],
    ])
    ->withComposerBased(
        laravel: true,
    )
    ->withSets([
        LaravelSetList::LARAVEL_CODE_QUALITY,
        LaravelSetList::LARAVEL_COLLECTION,
        LaravelSetList::LARAVEL_ELOQUENT_MAGIC_METHOD_TO_QUERY_BUILDER,
        LaravelSetList::LARAVEL_STATIC_TO_INJECTION,
    ])
    ->withRules([
        ConfigToTypedConfigMethodCallRector::class,
        ResponseHelperCallToJsonResponseRector::class,
        StaticFacadeToServiceMethodCallRector::class,
    ]);
