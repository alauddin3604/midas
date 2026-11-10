<?php

declare(strict_types=1);

namespace App\Http\Controllers\V1\Identity;

use App\Actions\Identity\RegisterUserAction;
use App\Http\Requests\V1\Identity\RegisterRequest;
use App\Http\V1\Controllers\ApiController;
use App\Http\V1\Resources\Identity\UserResource;
use Symfony\Component\HttpFoundation\Response;

final class RegisterController extends ApiController
{
    public function __invoke(RegisterRequest $request, RegisterUserAction $action): Response
    {
        $user = $action->execute($request->payload());

        return UserResource::make($user)->response();
    }
}
