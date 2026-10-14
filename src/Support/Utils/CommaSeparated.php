<?php

declare(strict_types=1);

namespace Support\Utils;

final class CommaSeparated
{
    /**
     * Convert a comma-separated string into a list of trimmed values.
     *
     * Empty segments are removed.
     *
     * @param  string|null  $value  The comma-separated string to convert.
     * @return list<string>
     */
    public static function toList(?string $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        return array_values(array_filter(array_map(
            trim(...),
            explode(',', $value),
        )));
    }
}
