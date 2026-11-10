<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;

abstract readonly class Event implements ShouldDispatchAfterCommit {}
