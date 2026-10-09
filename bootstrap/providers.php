<?php

use App\Audit\Providers\AuditServiceProvider;
use App\Identity\Providers\IdentityServiceProvider;
use Support\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    IdentityServiceProvider::class,
    AuditServiceProvider::class,
];
