<?php

declare(strict_types=1);

namespace App\Http\Controllers\V1\Identity;

use App\Http\Controllers\V1\ApiController;
use App\Http\Requests\V1\Identity\ListUsersRequest;
use App\Http\Resources\V1\Identity\UserResource;
use App\Models\Identity\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Attributes\Controllers\Authorize;

final class ListUsersController extends ApiController
{
    #[Authorize('viewAny', User::class)]
    public function __invoke(ListUsersRequest $request): JsonResponse
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
