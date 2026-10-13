<?php

declare(strict_types=1);

namespace Interfaces\Identity\Http\Api\V1\Controllers;

use Domain\Identity\Actions\RegisterUserAction;
use Interfaces\Identity\Http\Api\V1\Requests\RegisterRequest;
use Interfaces\Identity\Http\Api\V1\Resources\UserResource;
use Support\Bases\Controllers\Controller;

final class RegisterController extends Controller
{
    public function __invoke(RegisterRequest $request, RegisterUserAction $action): UserResource
    {
        return new UserResource($action->execute($request->payload()));
    }
}
