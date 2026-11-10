<?php

declare(strict_types=1);

namespace App\Actions\Identity;

use App\Events\Identity\UserDeleted;
use App\Models\Identity\User;
use Illuminate\Contracts\Events\Dispatcher;

final readonly class PerformUserDeletionAction
{
    public function __construct(
        private Dispatcher $dispatcher,
    ) {}

    public function execute(User $user, ?string $actorId = null): void
    {
        $user->tokens()->delete();
        $user->delete();

        $this->dispatcher->dispatch(new UserDeleted($user->id, $user->email, $actorId));
    }
}
