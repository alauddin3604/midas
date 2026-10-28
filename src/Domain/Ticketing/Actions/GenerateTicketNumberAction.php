<?php

declare(strict_types=1);

namespace Domain\Ticketing\Actions;

use DateTimeInterface;
use Domain\Ticketing\Models\TicketSequence;
use Domain\Ticketing\ValueObjects\TicketNumber;
use Illuminate\Support\Facades\DB;

final readonly class GenerateTicketNumberAction
{
    /**
     * Execute the action.
     */
    public function execute(DateTimeInterface $date): TicketNumber
    {
        return TicketNumber::generate($date, $this->nextSequence($date));
    }

    /**
     * The next sequence for the day.
     */
    private function nextSequence(DateTimeInterface $date): int
    {
        $day = $date->format('Y-m-d');

        return DB::transaction(function () use ($day): int {
            TicketSequence::query()->insertOrIgnore([
                'date' => $day,
                'last_number' => 0,
            ]);

            $row = TicketSequence::query()
                ->where('date', $day)
                ->lockForUpdate()
                ->firstOrFail();

            $row->increment('last_number');

            return $row->last_number;
        });
    }
}
