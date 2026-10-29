<?php

declare(strict_types=1);

namespace App\Policies\Ticketing;

final class TicketPolicy
{
    public function create(): bool
    {
        return true;
    }
}
