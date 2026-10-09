<?php

declare(strict_types=1);

namespace Domain\Identity\Payloads;

use Domain\Identity\Enums\Role;
use Illuminate\Contracts\Support\Arrayable;
use Override;

/**
 * @implements Arrayable<string, string|Role>
 */
final readonly class RegisterUserPayload implements Arrayable
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public Role $role = Role::Member,
    ) {}

    /**
     * @return array{name: string, email: string, password: string, role: Role}
     */
    #[Override]
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'role' => $this->role,
        ];
    }
}
