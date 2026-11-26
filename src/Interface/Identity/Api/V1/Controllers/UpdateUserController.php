<?php

declare(strict_types=1);

namespace Interface\Identity\Api\V1\Controllers;

use Application\Identity\Actions\UpdateUserAction;
use Domain\Identity\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Routing\Attributes\Controllers\Authorize;
use Interface\Identity\Api\V1\Requests\UpdateUserRequest;
use Symfony\Component\HttpFoundation\Response;

final class UpdateUserController
{
    public function __construct(
        private readonly ResponseFactory $response,
    ) {}

    #[Authorize('update', 'user')]
    public function __invoke(
        #[CurrentUser]
        User $actor,
        UpdateUserRequest $request,
        UpdateUserAction $action,
        User $user,
    ): Response {
        $action->execute($actor, $user, $request->toPayload());

        return $this->response->noContent();
    }
}
