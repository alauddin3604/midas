<?php

declare(strict_types=1);

namespace App\Console;

use Illuminate\Console\Command;
use ReflectionClass;

final class DomainCommandDiscovery
{
    /**
     * @return list<non-falsy-string>
     */
    public static function classes(): array
    {
        $files = glob(base_path('src/Interfaces/*/Console/*.php')) ?: [];
        sort($files);

        $classes = [];

        foreach ($files as $file) {
            $class = 'Interfaces\\'.str_replace(
                ['/', '.php'],
                ['\\', ''],
                substr($file, strlen(base_path('src/Interfaces')) + 1),
            );

            if (self::isCommandClass($class)) {
                $classes[] = $class;
            }
        }

        return $classes;
    }

    /**
     * Determine if the scanned class is a Command class.
     */
    private static function isCommandClass(string $class): bool
    {
        return is_subclass_of($class, Command::class)
            && ! (new ReflectionClass($class))->isAbstract();
    }
}
