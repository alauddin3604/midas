<?php

declare(strict_types=1);

namespace Interface\Identity\Api\V1\Controllers;

use Application\Identity\Actions\AuthenticateUserAction;
use Application\Identity\Exceptions\InvalidCredentials;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Interface\Identity\Api\V1\Requests\LoginRequest;
use Symfony\Component\HttpFoundation\Response;

final class LoginController
{
    public function __invoke(LoginRequest $request, AuthenticateUserAction $action): Response
    {
        try {
            $user = $action->execute($request->payload());
        } catch (InvalidCredentials $exception) {
            throw ValidationException::withMessages(['email' => [$exception->getMessage()]]);
        }

        $token = $user->createToken(Str::limit($request->userAgent() ?? $user->email, 255, ''));

        return new JsonResponse([
            'data' => [
                'token' => $token->plainTextToken,
            ],
        ]);
    }
}
