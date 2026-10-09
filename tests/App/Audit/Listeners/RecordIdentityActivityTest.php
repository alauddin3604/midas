<?php

declare(strict_types=1);

use Domain\Audit\Models\AuditEntry;
use Domain\Identity\Events\PasswordReset;
use Domain\Identity\Events\PasswordResetLinkRequested;
use Domain\Identity\Events\UserAuthenticated;
use Domain\Identity\Events\UserAuthenticationFailed;
use Domain\Identity\Events\UserDeleted;
use Domain\Identity\Events\UserRegistered;
use Domain\Identity\Events\UserUpdated;
use Domain\Identity\Models\User;
use Illuminate\Support\Facades\Context;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;

it('records an updated user with the actor and the changes', function (): void {
    event(new UserUpdated('user-1', 'actor-1', ['role' => ['old' => 'member', 'new' => 'admin']]));

    $entry = AuditEntry::query()->sole();

    expect($entry)
        ->event->toBe('identity.user.updated')
        ->actor_id->toBe('actor-1')
        ->subject_type->toBe('user')
        ->subject_id->toBe('user-1')
        ->changes->toBe(['role' => ['old' => 'member', 'new' => 'admin']]);
});

it('records a deleted user keeping their email', function (): void {
    event(new UserDeleted('user-1', 'jane@example.com', null));

    expect(AuditEntry::query()->sole())
        ->event->toBe('identity.user.deleted')
        ->actor_id->toBeNull()
        ->changes->toBe(['email' => ['old' => 'jane@example.com', 'new' => null]]);
});

it('attributes a registration to the signed-in admin', function (): void {
    $admin = User::factory()->admin()->create();
    actingAs($admin);

    event(new UserRegistered('user-1', 'jane@example.com', 'member'));

    expect(AuditEntry::query()->sole())
        ->event->toBe('identity.user.registered')
        ->actor_id->toBe($admin->id)
        ->changes->toBe(['role' => ['old' => null, 'new' => 'member']]);
});

it('leaves a self-registration without an actor', function (): void {
    event(new UserRegistered('user-1', 'jane@example.com', 'member'));

    expect(AuditEntry::query()->sole()->actor_id)->toBeNull();
});

it('attributes self-service events to the user themselves', function (object $event, string $name): void {
    event($event);

    expect(AuditEntry::query()->sole())
        ->event->toBe($name)
        ->actor_id->toBe('user-1')
        ->subject_type->toBe('user')
        ->subject_id->toBe('user-1');
})->with([
    'authenticated' => [new UserAuthenticated('user-1'), 'identity.user.authenticated'],
    'password reset' => [new PasswordReset('user-1'), 'identity.password.reset'],
]);

it('records anonymous events against the email', function (object $event, string $name): void {
    event($event);

    expect(AuditEntry::query()->sole())
        ->event->toBe($name)
        ->actor_id->toBeNull()
        ->subject_type->toBe('email')
        ->subject_id->toBe('jane@example.com');
})->with([
    'authentication failed' => [new UserAuthenticationFailed('jane@example.com'), 'identity.user.authentication_failed'],
    'reset link requested' => [new PasswordResetLinkRequested('jane@example.com'), 'identity.password.reset_link_requested'],
]);

it('stores the captured request context', function (): void {
    Context::addHidden('audit', ['ip' => '10.0.0.1', 'user_agent' => 'Pest', 'url' => 'http://localhost/login']);

    event(new UserAuthenticated('user-1'));

    expect(AuditEntry::query()->sole()->context)
        ->toBe(['ip' => '10.0.0.1', 'user_agent' => 'Pest', 'url' => 'http://localhost/login']);
});

it('records nothing for unrelated events', function (): void {
    event('some.other.event');

    assertDatabaseCount('audit_entries', 0);
});
