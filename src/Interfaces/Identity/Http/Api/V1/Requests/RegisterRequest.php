<?php

declare(strict_types=1);

namespace Interfaces\Identity\Http\Api\V1\Requests;

use Domain\Identity\Payloads\RegisterUserPayload;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

final class RegisterRequest extends FormRequest
{
    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', Password::defaults()],
        ];
    }

    /**
     * The role is never read from the request: self-registration always creates a member.
     */
    public function payload(): RegisterUserPayload
    {
        return new RegisterUserPayload(
            name: $this->string('name')->toString(),
            email: $this->string('email')->toString(),
            password: $this->string('password')->toString(),
        );
    }
}
