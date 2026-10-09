<?php

declare(strict_types=1);

namespace App\Identity\Http\Api\V1\Controllers;

use App\Identity\Http\Api\V1\Requests\LoginRequest;
use App\Identity\Http\Api\V1\Resources\UserResource;
use Domain\Identity\Actions\AuthenticateUserAction;
use Domain\Identity\Exceptions\InvalidCredentials;
use Illuminate\Auth\AuthManager;
use Illuminate\Validation\ValidationException;
use Support\Bases\Controllers\Controller;

final class LoginController extends Controller
{
    public function __invoke(LoginRequest $request, AuthenticateUserAction $action, AuthManager $auth): UserResource
    {
        try {
            $user = $action->execute($request->payload());
        } catch (InvalidCredentials $exception) {
            throw ValidationException::withMessages(['email' => [$exception->getMessage()]]);
        }

        $auth->guard('web')->login($user);
        $request->session()->regenerate();

        return new UserResource($user);
    }
}
