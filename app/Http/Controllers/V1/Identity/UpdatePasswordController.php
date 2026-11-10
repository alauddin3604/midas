<?php

declare(strict_types=1);

namespace App\Http\Controllers\V1\Identity;

use App\Actions\Identity\UpdateUserPasswordAction;
use App\Http\V1\Controllers\ApiController;
use App\Http\V1\Requests\Identity\UpdateUserPasswordRequest;
use App\Models\Identity\User;
use Illuminate\Container\Attributes\CurrentUser;
use Symfony\Component\HttpFoundation\Response;

final class UpdatePasswordController extends ApiController
{
    public function __invoke(
        #[CurrentUser]
        User $user,
        UpdateUserPasswordRequest $request,
        UpdateUserPasswordAction $action,
    ): Response {
        $action->execute($user, $request->toPayload());

        return response()->noContent();
    }
}
