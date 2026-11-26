<?php

declare(strict_types=1);

namespace Application\Identity\Actions;

use Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final readonly class DeleteUserFromEmailAction
{
    public function __construct(
        private PerformUserDeletionAction $performUserDeletion,
    ) {}

    /**
     * @throws ModelNotFoundException
     */
    public function execute(string $email): void
    {
        $user = User::query()->where('email', $email)->firstOrFail();

        $this->performUserDeletion->execute($user);
    }
}
