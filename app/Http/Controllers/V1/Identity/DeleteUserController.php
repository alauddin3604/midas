<?php

declare(strict_types=1);

namespace App\Http\Controllers\V1\Identity;

use App\Actions\Identity\DeleteUserAction;
use App\Http\Controllers\V1\ApiController;
use App\Models\Identity\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Contracts\Routing\ResponseFactory;
use Symfony\Component\HttpFoundation\Response;

final class DeleteUserController extends ApiController
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
