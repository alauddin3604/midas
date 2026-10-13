<?php

use App\Providers\AppServiceProvider;
use Interfaces\Audit\Providers\AuditServiceProvider;
use Interfaces\Identity\Providers\IdentityServiceProvider;

return [
    AppServiceProvider::class,
    IdentityServiceProvider::class,
    AuditServiceProvider::class,
];
