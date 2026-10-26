<?php

declare(strict_types=1);

namespace Domain\Identity\Enums;

enum Permission: string
{
    case AccessDashboard = 'dashboard.access';
    case ManageUsers = 'users.manage';
}
