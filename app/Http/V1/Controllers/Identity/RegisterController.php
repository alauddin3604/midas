<?php

declare(strict_types=1);

namespace App\Http\V1\Controllers\Identity;

use App\Http\V1\Controllers\ApiController;
use App\Http\V1\Requests\Identity\RegisterRequest;
use App\Http\V1\Resources\Identity\UserResource;
use Domain\Identity\Actions\RegisterUserAction;
use Illuminate\Http\JsonResponse;

final class RegisterController extends ApiController
{
    public function __invoke(RegisterRequest $request, RegisterUserAction $action): JsonResponse
    {
        $user = $action->execute($request->payload());

        return UserResource::make($user)->response();
    }
}
