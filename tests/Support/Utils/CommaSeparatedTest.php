<?php

declare(strict_types=1);

use Support\Utils\CommaSeparated;

use function PHPUnit\Framework\assertSame;

it('converts comma separated values to a list', function (?string $value, array $expected): void {
    assertSame($expected, CommaSeparated::toList($value));
})->with([
    'null' => [null, []],
    'empty string' => ['', []],
    'blank space' => [' ', []],
    'single value' => ['category', ['category']],
    'multiple values' => ['category,merchant', ['category', 'merchant']],
    'trims whitespace' => [' category , merchant ', ['category', 'merchant']],
    'removes empty segments' => ['category,,merchant,', ['category', 'merchant']],
    'comma at the beginning' => [',category', ['category']],
    'only commas and spaces' => [' , , ', []],
]);
