<?php

declare(strict_types=1);

namespace App\Http\V1\Requests\Identity;

use Domain\Identity\Enums\Role;
use Domain\Identity\Payloads\RegisterUserPayload;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

final class CreateUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', Password::defaults()],
            'role' => ['required', 'string', Rule::enum(Role::class)],
        ];
    }

    public function payload(): RegisterUserPayload
    {
        return new RegisterUserPayload(
            $this->input('name'),
            $this->input('email'),
            $this->input('password'),
            $this->enum('role', Role::class),
        );
    }
}
