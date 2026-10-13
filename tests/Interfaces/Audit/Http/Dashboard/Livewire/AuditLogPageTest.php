<?php

declare(strict_types=1);

use Database\Factories\AuditEntryFactory;
use Domain\Identity\Models\User;
use Interfaces\Audit\Http\Dashboard\Livewire\AuditLogPage;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('redirects guests to the login page', function (): void {
    get('/audit-log')->assertRedirectToRoute('login');
});

it('forbids members from the audit log', function (): void {
    actingAs(User::factory()->create())
        ->get('/audit-log')
        ->assertForbidden();
});

it('lists entries for admins with names and changes', function (): void {
    $admin = User::factory()->admin()->create(['name' => 'Ada Admin']);
    $subject = User::factory()->create(['name' => 'Sam Subject']);
    AuditEntryFactory::new()->create([
        'event' => 'identity.user.updated',
        'actor_id' => $admin->id,
        'subject_type' => 'user',
        'subject_id' => $subject->id,
        'changes' => ['role' => ['old' => 'member', 'new' => 'admin']],
        'context' => ['ip' => '10.0.0.1'],
    ]);

    actingAs($admin)
        ->get('/audit-log')
        ->assertOk()
        ->assertSeeLivewire(AuditLogPage::class)
        ->assertSeeText('identity.user.updated')
        ->assertSeeText('Ada Admin')
        ->assertSeeText('Sam Subject')
        ->assertSeeText('role: member → admin')
        ->assertSeeText('10.0.0.1');
});

it('shows system as the actor when there is none', function (): void {
    actingAs(User::factory()->admin()->create());
    AuditEntryFactory::new()->create(['actor_id' => null]);

    Livewire::test(AuditLogPage::class)->assertSeeText('System');
});

it('filters by event, actor and date range', function (): void {
    $admin = User::factory()->admin()->create();
    $other = User::factory()->create();
    actingAs($admin);

    AuditEntryFactory::new()->create(['event' => 'identity.user.deleted', 'actor_id' => $admin->id, 'occurred_at' => '2026-10-05 12:00:00']);
    AuditEntryFactory::new()->create(['event' => 'identity.user.updated', 'actor_id' => $admin->id, 'occurred_at' => '2026-10-05 12:00:00']);
    AuditEntryFactory::new()->create(['event' => 'identity.user.updated', 'actor_id' => $other->id, 'occurred_at' => '2026-10-05 12:00:00']);
    AuditEntryFactory::new()->create(['event' => 'identity.user.updated', 'actor_id' => $admin->id, 'occurred_at' => '2026-09-01 12:00:00']);

    $page = Livewire::test(AuditLogPage::class);
    expect($page->instance()->entries)->toHaveCount(4);

    $page->set('event', 'identity.user.updated');
    expect($page->instance()->entries)->toHaveCount(3);

    $page->set('actor', $admin->id);
    expect($page->instance()->entries)->toHaveCount(2);

    $page->set('from', '2026-10-01')->set('until', '2026-10-05');
    expect($page->instance()->entries)->toHaveCount(1);
});

it('includes entries from the whole last day of the range', function (): void {
    actingAs(User::factory()->admin()->create());
    AuditEntryFactory::new()->create(['occurred_at' => '2026-10-05 23:30:00']);

    $page = Livewire::test(AuditLogPage::class)->set('until', '2026-10-05');

    expect($page->instance()->entries)->toHaveCount(1);
});

it('paginates entries and returns to the first page when a filter changes', function (): void {
    actingAs(User::factory()->admin()->create());
    AuditEntryFactory::new()->count(30)->create();

    Livewire::test(AuditLogPage::class)
        ->call('gotoPage', 2)
        ->set('event', 'identity.user.updated')
        ->assertSet('paginators.page', 1);
});

it('links to the audit log from the sidebar for admins only', function (): void {
    actingAs(User::factory()->admin()->create())->get('/dashboard')->assertSee(route('audit.index'));
    actingAs(User::factory()->create())->get('/dashboard')->assertDontSee(route('audit.index'));
});
