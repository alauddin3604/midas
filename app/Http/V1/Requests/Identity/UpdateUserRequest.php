<?php

declare(strict_types=1);

namespace App\Http\V1\Requests\Identity;

use Domain\Identity\Enums\Role;
use Domain\Identity\Payloads\UpdateUserPayload;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required'],
            'email' => ['sometimes', 'required', 'email'],
            'role' => ['sometimes', 'nullable', Rule::enum(Role::class)],
        ];
    }

    public function toPayload(): UpdateUserPayload
    {
        return new UpdateUserPayload(
            $this->safe()->input('name'),
            $this->safe()->input('email'),
            $this->enum('role', Role::class),
        );
    }
}
