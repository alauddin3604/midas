<?php

declare(strict_types=1);

namespace Interface\Identity\Api\V1\Controllers;

use Application\Identity\Actions\RegisterUserAction;
use Domain\Identity\Models\User;
use Illuminate\Routing\Attributes\Controllers\Authorize;
use Interface\Identity\Api\V1\Requests\CreateUserRequest;
use Interface\Identity\Api\V1\Resources\UserResource;
use Symfony\Component\HttpFoundation\Response;

final class CreateUserController
{
    #[Authorize('create', User::class)]
    public function __invoke(CreateUserRequest $request, RegisterUserAction $action): Response
    {
        return UserResource::make($action->execute($request->payload()))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
