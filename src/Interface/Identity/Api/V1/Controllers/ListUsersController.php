<?php

declare(strict_types=1);

namespace Interface\Identity\Api\V1\Controllers;

use Domain\Identity\Models\User;
use Illuminate\Routing\Attributes\Controllers\Authorize;
use Interface\Identity\Api\V1\Requests\ListUsersRequest;
use Interface\Identity\Api\V1\Resources\UserResource;
use Symfony\Component\HttpFoundation\Response;

final class ListUsersController
{
    #[Authorize('viewAny', User::class)]
    public function __invoke(ListUsersRequest $request): Response
    {
        $users = User::query()
            ->search($request->validated('search'))
            ->orderBy('name')
            ->orderBy('id')
            ->paginate($request->integer('per_page', 15))
            ->withQueryString();

        return UserResource::collection($users)->response();
    }
}
