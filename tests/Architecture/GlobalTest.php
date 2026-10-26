<?php

declare(strict_types=1);

arch('every source file declares strict types')
    ->expect(['App', 'Domain', 'Interfaces', 'Support'])
    ->toUseStrictTypes();

arch('no debugging leftovers')
    ->expect(['dd', 'dump', 'ray', 'var_dump', 'print_r', 'die', 'exit'])
    ->not->toBeUsed();

arch('no dangerous or deprecated functions')
    ->expect(['eval', 'exec', 'shell_exec', 'system', 'passthru'])
    ->not->toBeUsed();

arch('enums are backed enums')
    ->expect('Domain\*\Enums')
    ->toBeEnums();

arch('support is the bottom layer')
    ->expect('Support')
    ->not->toUse(['App', 'Interfaces', 'Domain']);

arch('domain does not depend on delivery or wiring')
    ->expect('Domain')
    ->not->toUse([
        'App',
        'Interfaces',
        'Livewire',
        'Support\Http',
        'Illuminate\Http',
        'Illuminate\Routing',
        'Illuminate\Console',
    ]);

arch('interfaces never depend on the app layer')
    ->expect('Interfaces')
    ->not->toUse('App');

arch('nothing depends on Midas')
    ->expect('Interfaces')
    ->not->toUse('Interfaces\Midas')
    ->ignoring('Interfaces\Midas');
