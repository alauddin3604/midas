<?php

declare(strict_types=1);

namespace App\Enums\Identity;

enum Permission: string
{
    case ManageUsers = 'users.manage';
    case AssignTickets = 'tickets.assign';
}
