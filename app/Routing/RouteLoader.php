<?php

declare(strict_types=1);

namespace App\Routing;

use Illuminate\Support\Facades\Route;

final class RouteLoader
{
    /**
     * Load all the routes from the routes directory and register them with the router.
     */
    public static function load(): void
    {
        foreach (glob(base_path('routes/api/*'), GLOB_ONLYDIR) ?: [] as $dir) {
            $version = basename($dir);

            foreach (self::files("api/{$version}") as $file) {
                Route::middleware('api')
                    ->prefix("api/{$version}")
                    ->name("api.{$version}.")
                    ->group($file);
            }
        }
    }

    /**
     * @return list<string>
     */
    private static function files(string $directory): array
    {
        $files = glob(base_path("routes/{$directory}/*.php")) ?: [];
        sort($files);

        return $files;
    }
}
