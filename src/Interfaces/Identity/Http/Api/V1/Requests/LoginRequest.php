<?php

declare(strict_types=1);

namespace Interfaces\Identity\Http\Api\V1\Requests;

use Domain\Identity\Payloads\AuthenticateUserPayload;
use Illuminate\Foundation\Http\FormRequest;

final class LoginRequest extends FormRequest
{
    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function payload(): AuthenticateUserPayload
    {
        return new AuthenticateUserPayload(
            email: $this->string('email')->toString(),
            password: $this->string('password')->toString(),
        );
    }
}
