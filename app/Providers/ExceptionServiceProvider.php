<?php

declare(strict_types=1);

namespace App\Providers;

use App\Exceptions\Domain\DomainExceptionsRegistry;
use App\Exceptions\Domain\IdentityExceptionsMapper;
use App\Exceptions\Domain\TicketingExceptionsMapper;
use Illuminate\Support\ServiceProvider;

class ExceptionServiceProvider extends ServiceProvider
{
    public const TAG = 'domain.exception.mappers';

    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->tag([
            IdentityExceptionsMapper::class,
            TicketingExceptionsMapper::class,
        ], self::TAG);

        $this->app->when(DomainExceptionsRegistry::class)
            ->needs('$mappers')
            ->giveTagged(self::TAG);

        $this->app->singleton(DomainExceptionsRegistry::class);
    }
}
