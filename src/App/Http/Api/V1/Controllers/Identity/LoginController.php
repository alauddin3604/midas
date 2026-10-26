<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Controllers\Identity;

use App\Http\Api\V1\Controllers\ApiController;
use App\Http\Api\V1\Requests\Identity\LoginRequest;
use Domain\Identity\Actions\AuthenticateUserAction;
use Domain\Identity\Exceptions\InvalidCredentials;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class LoginController extends ApiController
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
