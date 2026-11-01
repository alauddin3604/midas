<?php

declare(strict_types=1);

namespace App\Http\V1\Controllers\Identity;

use App\Http\V1\Controllers\ApiController;
use Domain\Identity\Actions\DeleteAccessTokenAction;
use Domain\Identity\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class LogoutController extends ApiController
{
    public function __invoke(Request $request, DeleteAccessTokenAction $action): Response
    {
        /** @var User $user */
        $user = $request->user();

        $action->execute($user->currentAccessToken());

        return response()->noContent();
    }
}
