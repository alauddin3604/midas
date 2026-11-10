<?php

declare(strict_types=1);

namespace App\Http\Requests\V1\Identity;

use App\Enums\Identity\Role;
use App\Payloads\Identity\UpdateUserPayload;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateUserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required'],
            'email' => ['sometimes', 'required', 'email', Rule::unique('users')->ignore($this->route('user'))],
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
