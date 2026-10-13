<?php

declare(strict_types=1);

use Database\Factories\AuditEntryFactory;
use Domain\Audit\Models\AuditEntry;
use Illuminate\Console\Scheduling\Event as ScheduledEvent;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\travelTo;

it('schedules the pruning of expired audit entries daily', function (): void {
    $event = collect(app(Schedule::class)->events())
        ->first(fn (ScheduledEvent $event): bool => str_contains((string) $event->command, 'model:prune')
            && str_contains((string) $event->command, 'AuditEntry'));

    expect($event)->not->toBeNull()
        ->and($event->expression)->toBe('0 0 * * *');
});

it('prunes only the entries older than the retention period', function (): void {
    config()->set('audit.retention_days', 30);
    travelTo('2026-10-10 12:00:00');

    $expired = AuditEntryFactory::new()->create(['occurred_at' => now()->subDays(31)]);
    $kept = AuditEntryFactory::new()->create(['occurred_at' => now()->subDays(29)]);

    Artisan::call('model:prune', ['--model' => AuditEntry::class]);

    assertDatabaseCount('audit_entries', 1);
    expect(AuditEntry::query()->sole()->is($kept))->toBeTrue()
        ->and(AuditEntry::query()->whereKey($expired->id)->exists())->toBeFalse();
});
