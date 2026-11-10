<?php

declare(strict_types=1);

namespace App\Http\Controllers\V1\Identity;

use App\Actions\Identity\AuthenticateUserAction;
use App\Exceptions\Identity\InvalidCredentials;
use App\Http\Controllers\V1\ApiController;
use App\Http\Requests\V1\Identity\LoginRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

final class LoginController extends ApiController
{
    public function __invoke(LoginRequest $request, AuthenticateUserAction $action): Response
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
