<?php

declare(strict_types=1);

use App\Http\Controllers\V1\ApiController;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

arch('Actions are final, readonly classes suffixed with Action')
    ->expect('App\Actions')
    ->toBeClasses()
    ->toBeFinal()
    ->toBeReadonly()
    ->toHaveSuffix('Action')
    ->toHaveMethod('execute');

arch('Actions are not using Request object')
    ->expect('App\Actions')
    ->not->toUse([
        Request::class,
    ]);

arch('console commands are final and extend Command')
    ->expect('App\Console\Commands')
    ->toBeClasses()
    ->toBeFinal()
    ->toExtend(Command::class)
    ->toHaveSuffix('Command');

arch('API controllers are final, invokable and extend ApiController')
    ->expect('App\Http\Controllers\V1\*')
    ->toBeClasses()
    ->toBeFinal()
    ->toBeInvokable()
    ->toExtend(ApiController::class)
    ->toHaveSuffix('Controller');

arch('Payloads must be final, readonly classes suffixed with Payload')
    ->expect('App\Payloads\*')
    ->toBeClasses()
    ->toBeFinal()
    ->toBeReadonly()
    ->toHaveSuffix('Payload');

arch('Query builders must be final Eloquent builders suffixed with QueryBuilder')
    ->expect('App\QueryBuilders\*')
    ->toBeClasses()
    ->toBeFinal()
    ->toExtend(Builder::class)
    ->toHaveSuffix('QueryBuilder');

arch('Support does not depend on App')
    ->expect('App\Support')
    ->not->toUse([
        'App',
    ]);
