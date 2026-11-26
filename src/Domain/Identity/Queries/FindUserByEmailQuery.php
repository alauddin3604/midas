<?php

declare(strict_types=1);

namespace Domain\Identity\Queries;

use Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final readonly class FindUserByEmailQuery
{
    /**
     * @throws ModelNotFoundException No user found with that email.
     */
    public function fetch(string $email): User
    {
        return User::query()
            ->where('email', $email)
            ->firstOrFail();
    }
}
