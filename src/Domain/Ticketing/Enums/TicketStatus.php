<?php

declare(strict_types=1);

namespace Domain\Ticketing\Enums;

use function in_array;

enum TicketStatus: string
{
    case New = 'new';
    case Assigned = 'assigned';
    case InProgress = 'in_progress';
    case OnHold = 'on_hold';
    case Resolved = 'resolved';
    case Closed = 'closed';

    /** @return list<self> */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::New => [self::Assigned, self::Closed],
            self::Assigned => [self::InProgress, self::Closed],
            self::InProgress => [self::OnHold, self::Resolved],
            self::OnHold => [self::InProgress],
            self::Resolved => [self::Closed, self::InProgress], // InProgress = reopen
            self::Closed => [],
        };
    }

    public function canTransitionTo(self $to): bool
    {
        return in_array($to, $this->allowedTransitions(), true);
    }

    public function requiresAssignee(): bool
    {
        return in_array($this, [self::Assigned, self::InProgress, self::OnHold, self::Resolved], true);
    }

    public function hasResolved(): bool
    {
        return $this === self::Resolved;
    }

    public function isTerminal(): bool
    {
        return $this === self::Closed;
    }
}
