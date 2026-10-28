<?php

namespace Database\Factories\Ticketing;

use Domain\Ticketing\Models\TicketSequence;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Date;

/**
 * @extends Factory<TicketSequence>
 */
#[UseModel(TicketSequence::class)]
class TicketSequenceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'date' => Date::now()->toDateString(),
            'last_number' => 0,
        ];
    }
}
