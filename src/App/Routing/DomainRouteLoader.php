<?php

declare(strict_types=1);

namespace App\Routing;

use Illuminate\Support\Facades\Route;

final class DomainRouteLoader
{
    private const string BASE = 'src/Interfaces';

    /**
     * Register every route file found under `src/App/<Domain>/Http/...`.
     *
     * Dashboard routes get the `web` middleware group. Each API version folder (e.g. `V1`)
     * gets the `api/<version>` prefix and the `api.<version>.` name prefix.
     */
    public static function load(): void
    {
        foreach (self::files('Http/Dashboard/Routes') as $file) {
            Route::middleware('web')->group($file);
        }

        foreach (self::apiVersions() as $version) {
            $slug = strtolower($version);

            foreach (self::files("Http/Api/{$version}/Routes") as $file) {
                Route::middleware('api')
                    ->prefix("api/{$slug}")
                    ->name("api.{$slug}.")
                    ->group($file);
            }
        }
    }

    /**
     * @return list<string>
     */
    private static function apiVersions(): array
    {
        $versions = array_map(
            basename(...),
            glob(base_path(self::BASE.'/*/Http/Api/*'), GLOB_ONLYDIR) ?: [],
        );

        $versions = array_unique($versions);
        sort($versions);

        return $versions;
    }

    /**
     * @return list<string>
     */
    private static function files(string $relativePath): array
    {
        $files = glob(base_path(self::BASE."/*/{$relativePath}/*.php")) ?: [];
        sort($files);

        return $files;
    }
}
