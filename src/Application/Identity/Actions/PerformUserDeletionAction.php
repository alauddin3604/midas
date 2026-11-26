<?php

declare(strict_types=1);

namespace Application\Identity\Actions;

use Domain\Identity\Events\UserDeleted;
use Domain\Identity\Models\User;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\DatabaseManager;

final readonly class PerformUserDeletionAction
{
    public function __construct(
        private DatabaseManager $database,
        private Dispatcher $dispatcher,
    ) {}

    public function execute(User $user, ?string $actorId = null): void
    {
        $this->database->transaction(function () use ($user, $actorId): void {
            $user->tokens()->delete();
            $user->delete();

            $this->dispatcher->dispatch(new UserDeleted($user->id, $user->email, $actorId));
        });
    }
}
