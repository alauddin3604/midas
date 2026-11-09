<?php

declare(strict_types=1);

namespace App\Actions\Identity;

use Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final readonly class DeleteUserFromEmailAction
{
    public function __construct(
        private PerformUserDeletion $performUserDeletion,
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
