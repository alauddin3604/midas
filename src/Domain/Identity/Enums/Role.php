<?php

declare(strict_types=1);

namespace Domain\Identity\Enums;

enum Role: string
{
    case Admin = 'admin';
    case Member = 'member';

    /**
     * @return list<Permission>
     */
    public function permissions(): array
    {
        return match ($this) {
            self::Admin => Permission::cases(),
            self::Member => [Permission::AccessDashboard],
        };
    }

    public function grants(Permission $permission): bool
    {
        return in_array($permission, $this->permissions(), true);
    }

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
