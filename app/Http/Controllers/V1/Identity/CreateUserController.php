<?php

declare(strict_types=1);

namespace App\Http\Controllers\V1\Identity;

use App\Actions\Identity\RegisterUserAction;
use App\Http\Requests\V1\Identity\CreateUserRequest;
use App\Http\Resources\V1\Identity\UserResource;
use App\Http\V1\Controllers\ApiController;
use App\Models\Identity\User;
use Illuminate\Routing\Attributes\Controllers\Authorize;
use Symfony\Component\HttpFoundation\Response;

final class CreateUserController extends ApiController
{
    #[Authorize('create', User::class)]
    public function __invoke(CreateUserRequest $request, RegisterUserAction $action): Response
    {
        return UserResource::make($action->execute($request->payload()))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
