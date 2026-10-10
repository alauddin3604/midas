<?php

declare(strict_types=1);

namespace App\Audit\Providers;

use App\Audit\Http\Middleware\CaptureAuditContext;
use App\Audit\Listeners\RecordIdentityActivity;
use Domain\Audit\Models\AuditEntry;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

final class AuditServiceProvider extends ServiceProvider
{
    /**
     * Subscribe to domain events, capture the request context for every request in the web group
     * (dashboard and API routes both use it) and schedule the pruning of expired entries.
     */
    public function boot(Router $router): void
    {
        Event::subscribe(RecordIdentityActivity::class);

        $router->pushMiddlewareToGroup('web', CaptureAuditContext::class);

        $this->callAfterResolving(
            Schedule::class,
            fn (Schedule $schedule) => $schedule
                ->command('model:prune', ['--model' => AuditEntry::class])
                ->daily(),
        );
    }
}
