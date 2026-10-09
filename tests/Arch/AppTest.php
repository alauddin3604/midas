<?php

declare(strict_types=1);

use Illuminate\Console\Command;
use Livewire\Component;
use Support\Bases\Controllers\Controller;

arch('Api controllers must be final and extend base Controller')
    ->expect('App\*\Http\Api\*\Controllers')
    ->toBeFinal()
    ->toExtend(Controller::class);

arch('Dashboard Livewire must be strict, final and extends Livewire Component')
    ->expect('App\*\Http\Dashboard\Livewire')
    ->toUseStrictTypes()
    ->toBeFinal()
    ->toExtend(Component::class);

arch('Console commands must be final, extend Command and declare strict types')
    ->expect('App\*\Console')
    ->toBeClasses()
    ->toBeFinal()
    ->toExtend(Command::class)
    ->toUseStrictTypes();
