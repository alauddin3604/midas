<?php

declare(strict_types=1);

namespace Interface\Identity\Api\V1\Controllers;

use Application\Identity\Actions\DeleteUserAction;
use Domain\Identity\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\Routing\ResponseFactory;
use Symfony\Component\HttpFoundation\Response;

final class DeleteUserController
{
    public function __construct(
        private readonly ResponseFactory $response,
    ) {}

    /**
     * Handle the incoming request.
     */
    public function __invoke(#[CurrentUser] User $actor, DeleteUserAction $action, User $user): Response
    {
        $action->execute($actor, $user);

        return $this->response->noContent();
    }
}
