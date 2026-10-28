<?php

declare(strict_types=1);

namespace App\Http\V1\Controllers\Identity;

use App\Http\V1\Controllers\ApiController;
use App\Http\V1\Requests\Identity\ListUsersRequest;
use App\Http\V1\Resources\Identity\UserResource;
use Domain\Identity\Models\User;
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
