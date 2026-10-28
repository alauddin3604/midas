<?php

declare(strict_types=1);

namespace Domain\Ticketing\Models;

use Database\Factories\Ticketing\TicketSequenceFactory;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $last_number The last number for the day.
 */
#[UseFactory(TicketSequenceFactory::class)]
#[Guarded([])]
#[WithoutTimestamps]
class TicketSequence extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['last_number' => 'integer'];
    }
}
