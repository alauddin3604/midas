<?php

declare(strict_types=1);

namespace Interfaces\Identity\Http\Api\V1\Controllers;

use Domain\Identity\Actions\AuthenticateUserAction;
use Domain\Identity\Exceptions\InvalidCredentials;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Interfaces\Identity\Http\Api\V1\Requests\LoginRequest;
use Support\Bases\Controllers\Controller;

final class LoginController extends Controller
{
    public function __invoke(LoginRequest $request, AuthenticateUserAction $action): JsonResponse
    {
        try {
            $user = $action->execute($request->payload());
        } catch (InvalidCredentials $exception) {
            throw ValidationException::withMessages(['email' => [$exception->getMessage()]]);
        }

        $token = $user->createToken(Str::limit($request->userAgent() ?? $user->email, 255, ''));

        return response()->json([
            'data' => [
                'token' => $token->plainTextToken,
            ],
        ]);
    }
}
