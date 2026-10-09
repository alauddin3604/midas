<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Support\Bases\Events\DomainEvent;
use Support\Bases\Exceptions\DomainException;

arch('Domain does not depend on the App layer')
    ->expect('Domain')
    ->not->toUse('App');

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

arch('Domain files declare strict types')
    ->expect('Domain')
    ->toUseStrictTypes();

arch('Domain actions must be final, readonly classes suffixed with Action, and have execute method')
    ->expect('Domain\*\Actions')
    ->toBeClasses()
    ->toBeFinal()
    ->toBeReadonly()
    ->toHaveSuffix('Action')
    ->toHaveMethod('execute');

arch('Domain payloads must be final, readonly classes suffixed with Payload')
    ->expect('Domain\*\Payloads')
    ->toBeClasses()
    ->toBeFinal()
    ->toBeReadonly()
    ->toHaveSuffix('Payload');

arch('Domain exceptions must be final and extend the base domain exception')
    ->expect('Domain\*\Exceptions')
    ->toBeClasses()
    ->toBeFinal()
    ->toExtend(DomainException::class);

arch('Domain models are Eloquent models that stay free of business operations')
    ->expect('Domain\*\Models')
    ->toBeClasses()
    ->toExtend(Model::class)
    ->not->toUse('Domain\*\Actions');

arch('Domain enums must be enums')
    ->expect('Domain\*\Enums')
    ->toBeEnums();

arch('Domain events must be final, readonly classes extending the base domain event')
    ->expect('Domain\*\Events')
    ->toBeClasses()
    ->toBeFinal()
    ->toBeReadonly()
    ->toExtend(DomainEvent::class);

arch('Domain events are dispatched with the event() helper, not the Dispatchable trait')
    ->expect('Domain\*\Events')
    ->not->toUse(Dispatchable::class);

arch('Audit domain stays generic and knows no other domain')
    ->expect('Domain\Audit')
    ->not->toUse('Domain\Identity');
