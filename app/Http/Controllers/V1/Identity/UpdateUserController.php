<?php

declare(strict_types=1);

namespace App\Http\Controllers\V1\Identity;

use App\Actions\Identity\UpdateUserAction;
use App\Http\Requests\V1\Identity\UpdateUserRequest;
use App\Http\V1\Controllers\ApiController;
use Domain\Identity\Models\User;
use Illuminate\Http\Response;
use Illuminate\Routing\Attributes\Controllers\Authorize;

final class UpdateUserController extends ApiController
{
    #[Authorize('update', 'user')]
    public function __invoke(
        UpdateUserRequest $request,
        UpdateUserAction $action,
        User $user,
    ): Response {
        $action->execute($request->user(), $user, $request->toPayload());

        return response()->noContent();
    }
}
