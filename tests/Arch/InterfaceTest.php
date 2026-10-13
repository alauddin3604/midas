<?php

declare(strict_types=1);

use Illuminate\Console\Command;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Resources\Json\JsonResource;
use Livewire\Component;
use Support\Bases\Controllers\Controller;

arch('interfaces never depend on the app layer')
    ->expect('Interfaces')
    ->not->toUse('App');

arch('only Midas may be used by nothing')
    ->expect('Interfaces')
    ->not->toUse('Interfaces\Midas')
    ->ignoring('Interfaces\Midas');

arch('interfaces do not use other domains models directly in delivery code')
    ->expect('Interfaces')
    ->not->toUse('Domain\*\QueryBuilders');

arch('API controllers are final and extend the base controller')
    ->expect('Interfaces\*\Http\Api\*\Controllers')
    ->toBeClasses()
    ->toBeFinal()
    ->toExtend(Controller::class)
    ->toHaveSuffix('Controller');

arch('API requests are final form requests')
    ->expect('Interfaces\*\Http\Api\*\Requests')
    ->toBeClasses()
    ->toBeFinal()
    ->toExtend(FormRequest::class)
    ->toHaveSuffix('Request');

arch('API resources are final JSON resources')
    ->expect('Interfaces\*\Http\Api\*\Resources')
    ->toBeClasses()
    ->toBeFinal()
    ->toExtend(JsonResource::class)
    ->toHaveSuffix('Resource');

arch('Livewire components are final and extends Component')
    ->expect('Interfaces\*\Http\Dashboard\Livewire')
    ->toBeClasses()
    ->toBeFinal()
    ->toExtend(Component::class);

arch('console commands are final and extend Command')
    ->expect('Interfaces\*\Console')
    ->toBeClasses()
    ->toBeFinal()
    ->toExtend(Command::class)
    ->toHaveSuffix('Command');

arch('listeners are final')
    ->expect('Interfaces\*\Listeners')
    ->toBeClasses()
    ->toBeFinal();
