<?php

declare(strict_types=1);

namespace App\Actions\Identity;

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
