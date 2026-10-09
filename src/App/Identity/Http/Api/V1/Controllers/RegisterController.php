<?php

declare(strict_types=1);

namespace App\Identity\Http\Api\V1\Controllers;

use App\Identity\Http\Api\V1\Requests\RegisterRequest;
use App\Identity\Http\Api\V1\Resources\UserResource;
use Domain\Identity\Actions\RegisterUserAction;
use Support\Bases\Controllers\Controller;

final class RegisterController extends Controller
{
    public function __invoke(RegisterRequest $request, RegisterUserAction $action): UserResource
    {
        return new UserResource($action->execute($request->payload()));
    }
}
