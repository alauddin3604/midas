<?php

declare(strict_types=1);

namespace App\Http\Controllers\V1\Identity;

use App\Actions\Identity\DeleteAccessTokenAction;
use App\Http\V1\Controllers\ApiController;
use App\Models\Identity\User;
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
