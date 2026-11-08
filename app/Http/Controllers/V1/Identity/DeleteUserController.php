<?php

declare(strict_types=1);

namespace App\Http\Controllers\V1\Identity;

use App\Actions\Identity\DeleteUser;
use Domain\Identity\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Symfony\Component\HttpFoundation\Response;

final readonly class DeleteUserController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(#[CurrentUser] User $actor, DeleteUser $action, User $user): Response
    {
        $action->handle($actor, $user);

        return response()->noContent();
    }
}
