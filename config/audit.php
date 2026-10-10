<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Audit Entry Retention
    |--------------------------------------------------------------------------
    |
    | Audit entries older than this many days are deleted by the scheduled
    | `model:prune` run. Entries hold IP addresses and attempted emails, so
    | keep this only as long as you need them.
    |
    */

    'retention_days' => (int) env('AUDIT_RETENTION_DAYS', 365),

];
