<?php

declare(strict_types=1);

namespace Interface\Identity\Api\V1\Controllers;

use Application\Identity\Actions\UpdateUserPasswordAction;
use Domain\Identity\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\Routing\ResponseFactory;
use Interface\Identity\Api\V1\Requests\UpdateUserPasswordRequest;
use Symfony\Component\HttpFoundation\Response;

final class UpdatePasswordController
{
    public function __construct(
        private readonly ResponseFactory $response,
    ) {}

    public function __invoke(
        #[CurrentUser]
        User $user,
        UpdateUserPasswordRequest $request,
        UpdateUserPasswordAction $action,
    ): Response {
        $action->execute($user, $request->toPayload());

        return $this->response->noContent();
    }
}
