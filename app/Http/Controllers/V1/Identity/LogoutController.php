<?php

declare(strict_types=1);

namespace App\Http\Controllers\V1\Identity;

use App\Actions\Identity\DeleteAccessTokenAction;
use App\Http\Controllers\V1\ApiController;
use App\Models\Identity\User;
use Illuminate\Container\Attributes\CurrentUser;
use Symfony\Component\HttpFoundation\Response;

final class LogoutController extends ApiController
{
    public function __invoke(#[CurrentUser] User $user, DeleteAccessTokenAction $action): Response
    {
        $action->execute($user->currentAccessToken());

        return response()->noContent();
    }
}
