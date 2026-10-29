<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

$versions = glob(__DIR__.'/*', GLOB_ONLYDIR) ?: [];
sort($versions);

foreach ($versions as $dir) {
    $version = basename($dir);

    $files = glob("{$dir}/*.php") ?: [];
    sort($files);

    foreach ($files as $file) {
        Route::prefix($version)
            ->middleware('auth:sanctum')
            ->name("{$version}.")
            ->group($file);
    }
}
