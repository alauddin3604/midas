<?php

declare(strict_types=1);

namespace Interfaces\Identity\Http\Api\V1\Controllers;

use Domain\Identity\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Support\Bases\Controllers\Controller;

final class LogoutController extends Controller
{
    public function __invoke(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();

        $user->currentAccessToken()->delete();

        return response()->noContent();
    }
}
