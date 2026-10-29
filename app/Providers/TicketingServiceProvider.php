<?php

declare(strict_types=1);

namespace App\Providers;

use App\Policies\Ticketing\TicketPolicy;
use Domain\Ticketing\Models\Ticket;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class TicketingServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Gate::policy(Ticket::class, TicketPolicy::class);
    }
}
