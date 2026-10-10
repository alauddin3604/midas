<?php

declare(strict_types=1);

namespace App\Audit\Listeners;

use App\Audit\Http\Middleware\CaptureAuditContext;
use Domain\Audit\Actions\RecordAuditEntryAction;
use Domain\Audit\Payloads\RecordAuditEntryPayload;
use Domain\Identity\Events\PasswordReset;
use Domain\Identity\Events\PasswordResetLinkRequested;
use Domain\Identity\Events\UserAuthenticated;
use Domain\Identity\Events\UserAuthenticationFailed;
use Domain\Identity\Events\UserDeleted;
use Domain\Identity\Events\UserRegistered;
use Domain\Identity\Events\UserUpdated;
use Illuminate\Auth\AuthManager;
use Illuminate\Support\Facades\Context;
use Throwable;

/**
 * Translates Identity events into audit entries; the only place that knows both domains.
 */
final readonly class RecordIdentityActivity
{
    private const string USER = 'user';

    private const string EMAIL = 'email';

    public function __construct(
        private RecordAuditEntryAction $action,
        private AuthManager $auth,
    ) {}

    /**
     * @return array<class-string, string>
     */
    public function subscribe(): array
    {
        return [
            UserRegistered::class => 'handleUserRegistered',
            UserUpdated::class => 'handleUserUpdated',
            UserDeleted::class => 'handleUserDeleted',
            UserAuthenticated::class => 'handleUserAuthenticated',
            UserAuthenticationFailed::class => 'handleUserAuthenticationFailed',
            PasswordResetLinkRequested::class => 'handlePasswordResetLinkRequested',
            PasswordReset::class => 'handlePasswordReset',
        ];
    }

    public function handleUserRegistered(UserRegistered $event): void
    {
        $this->record(
            'identity.user.registered',
            self::USER,
            $event->userId,
            $this->webGuardUserId(),
            ['role' => ['old' => null, 'new' => $event->role]],
        );
    }

    public function handleUserUpdated(UserUpdated $event): void
    {
        $this->record('identity.user.updated', self::USER, $event->userId, $event->actorId, $event->changes);
    }

    public function handleUserDeleted(UserDeleted $event): void
    {
        $this->record(
            'identity.user.deleted',
            self::USER,
            $event->userId,
            $event->actorId,
            ['email' => ['old' => $event->email, 'new' => null]],
        );
    }

    public function handleUserAuthenticated(UserAuthenticated $event): void
    {
        $this->record('identity.user.authenticated', self::USER, $event->userId, $event->userId);
    }

    public function handleUserAuthenticationFailed(UserAuthenticationFailed $event): void
    {
        $this->record('identity.user.authentication_failed', self::EMAIL, $event->email);
    }

    public function handlePasswordResetLinkRequested(PasswordResetLinkRequested $event): void
    {
        $this->record('identity.password.reset_link_requested', self::EMAIL, $event->email);
    }

    public function handlePasswordReset(PasswordReset $event): void
    {
        $this->record('identity.password.reset', self::USER, $event->userId, $event->userId);
    }

    /**
     * Record the entry without ever breaking the operation that is being audited.
     *
     * @param  array<string, array{old: mixed, new: mixed}>|null  $changes
     */
    private function record(
        string $name,
        string $subjectType,
        string $subjectId,
        ?string $actorId = null,
        ?array $changes = null,
    ): void {
        try {
            $this->action->execute(new RecordAuditEntryPayload(
                event: $name,
                subjectType: $subjectType,
                subjectId: $subjectId,
                occurredAt: now(),
                actorId: $actorId,
                changes: $changes,
                context: Context::getHidden(CaptureAuditContext::KEY),
            ));
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private function webGuardUserId(): ?string
    {
        $id = $this->auth->guard('web')->id();

        return $id === null ? null : (string) $id;
    }
}
