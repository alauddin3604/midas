<?php

declare(strict_types=1);

namespace App\Policies\Ticketing;

use App\Models\Identity\User;
use Domain\Identity\Enums\Permission;
use Domain\Ticketing\Models\Ticket;

final class TicketPolicy
{
    public function create(): bool
    {
        return true;
    }

    public function assign(User $user, Ticket $ticket): bool
    {
        return $user->role->grants(Permission::AssignTickets);
    }
}
