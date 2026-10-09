<?php

declare(strict_types=1);

namespace Domain\Identity\Payloads;

use Domain\Identity\Enums\Role;
use Illuminate\Contracts\Support\Arrayable;
use Override;

/**
 * @implements Arrayable<string, string|Role>
 */
final readonly class UpdateUserPayload implements Arrayable
{
    public function __construct(
        public string $name,
        public string $email,
        public Role $role,
    ) {}

    /**
     * @return array{name: string, email: string, role: Role}
     */
    #[Override]
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
        ];
    }
}
