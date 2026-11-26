<?php

declare(strict_types=1);

namespace Interface\Identity\Api\V1\Controllers;

use Application\Identity\Actions\RegisterUserAction;
use Interface\Identity\Api\V1\Requests\RegisterRequest;
use Interface\Identity\Api\V1\Resources\UserResource;
use Symfony\Component\HttpFoundation\Response;

final class RegisterController
{
    public function __invoke(RegisterRequest $request, RegisterUserAction $action): Response
    {
        $user = $action->execute($request->payload());

        return UserResource::make($user)->response();
    }
}
