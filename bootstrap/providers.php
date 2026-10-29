<?php

use App\Providers\AppServiceProvider;
use App\Providers\ExceptionServiceProvider;
use App\Providers\IdentityServiceProvider;
use App\Providers\TicketingServiceProvider;

return [
    AppServiceProvider::class,
    ExceptionServiceProvider::class,
    IdentityServiceProvider::class,
    TicketingServiceProvider::class,
];
