<?php

declare(strict_types=1);

namespace Domain\Identity\Payloads;

use Illuminate\Contracts\Support\Arrayable;
use Override;

/**
 * @implements Arrayable<string, string>
 */
final readonly class ResetPasswordPayload implements Arrayable
{
    public function __construct(
        public string $email,
        public string $token,
        public string $password,
    ) {}

    /**
     * @return array{email: string, token: string, password: string}
     */
    #[Override]
    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'token' => $this->token,
            'password' => $this->password,
        ];
    }
}
