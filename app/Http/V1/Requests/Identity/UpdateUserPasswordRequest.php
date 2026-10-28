<?php

declare(strict_types=1);

namespace App\Http\V1\Requests\Identity;

use Domain\Identity\Payloads\UpdateUserPasswordPayload;
use Domain\Identity\ValueObjects\Password;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password as RulesPassword;

final class UpdateUserPasswordRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'password' => [
                'required',
                'string',
                'confirmed',
                RulesPassword::min(8)
                    ->mixedCase()
                    ->letters(),
            ],
        ];
    }

    public function toPayload(): UpdateUserPasswordPayload
    {
        return new UpdateUserPasswordPayload(
            password: Password::fromString($this->safe()->input('password')),
        );
    }
}
