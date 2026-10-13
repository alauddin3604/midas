<?php

declare(strict_types=1);

use Domain\Audit\Models\AuditEntry;
use Domain\Identity\Models\User;

use function Pest\Laravel\postJson;
use function Pest\Laravel\withHeader;

it('records the request context on audit entries made during a web request', function (): void {
    $user = User::factory()->create(['email' => 'jane@example.com']);

    withHeader('User-Agent', 'Pest/Audit')
        ->postJson('/api/v1/login', ['email' => 'jane@example.com', 'password' => 'password'])
        ->assertOk();

    $entry = AuditEntry::query()->where('event', 'identity.user.authenticated')->sole();

    expect($entry)
        ->subject_id->toBe($user->id)
        ->context->toMatchArray([
            'ip' => '127.0.0.1',
            'user_agent' => 'Pest/Audit',
            'url' => url('/api/v1/login'),
        ]);
});

it('records a failed login with the attempted email', function (): void {
    postJson('/api/v1/login', ['email' => 'jane@example.com', 'password' => 'wrong'])
        ->assertUnprocessable();

    expect(AuditEntry::query()->sole())
        ->event->toBe('identity.user.authentication_failed')
        ->subject_id->toBe('jane@example.com');
});
