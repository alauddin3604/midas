<?php

declare(strict_types=1);

namespace Domain\Identity\Actions;

use Laravel\Sanctum\PersonalAccessToken;

final readonly class DeleteAccessTokenAction
{
    /**
     * Delete the give PAT.
     */
    public function execute(PersonalAccessToken $token): void
    {
        $token->delete();
    }
}
