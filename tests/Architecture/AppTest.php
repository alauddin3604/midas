<?php

declare(strict_types=1);

use App\Http\V1\Controllers\ApiController;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

arch('console commands are final and extend Command')
    ->expect('App\Console\Commands')
    ->toBeClasses()
    ->toBeFinal()
    ->toExtend(Command::class)
    ->toHaveSuffix('Command');

arch('API controllers are final and extend ApiController')
    ->expect('App\Http\V1\Controllers')
    ->toBeClasses()
    ->toExtend(ApiController::class)
    ->toHaveSuffix('Controller');

arch('Query builders must be final Eloquent builders suffixed with QueryBuilder')
    ->expect('App\QueryBuilders\*')
    ->toBeClasses()
    ->toBeFinal()
    ->toExtend(Builder::class)
    ->toHaveSuffix('QueryBuilder');
