<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;

arch('Domain does not depend on delivery mechanisms')
    ->expect('Domain')
    ->not->toUse([
        'Livewire',
        'Illuminate\Http',
        'Illuminate\Routing',
        'Illuminate\Console',
        'Illuminate\Support\Facades\Auth',
        'Illuminate\Support\Facades\Request',
    ]);

arch('Domain models are Eloquent models that stay free of business operations')
    ->expect('Domain\*\Models')
    ->toBeClasses()
    ->toExtend(Model::class)
    ->not->toUse('Domain\*\Actions');

arch('Domain enums must be enums')
    ->expect('Domain\*\Enums')
    ->toBeEnums();

arch('Domain events are dispatched with the event() helper, not the Dispatchable trait')
    ->expect('Domain\*\Events')
    ->not->toUse(Dispatchable::class);

arch('Domain models keep query scopes in their query builder')
    ->expect('Domain\*\Models')
    ->not->toUse(Scope::class);
