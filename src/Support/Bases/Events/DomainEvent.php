<?php

declare(strict_types=1);

namespace Support\Bases\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;

abstract readonly class DomainEvent implements ShouldDispatchAfterCommit {}
