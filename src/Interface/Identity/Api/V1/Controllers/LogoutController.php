<?php

declare(strict_types=1);

namespace Interface\Identity\Api\V1\Controllers;

use Application\Identity\Actions\DeleteAccessTokenAction;
use Domain\Identity\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\Routing\ResponseFactory;
use Symfony\Component\HttpFoundation\Response;

final class LogoutController
{
    public function __construct(
        private readonly ResponseFactory $response,
    ) {}

    public function __invoke(#[CurrentUser] User $user, DeleteAccessTokenAction $action): Response
    {
        $action->execute($user->currentAccessToken());

        return $this->response->noContent();
    }
}
