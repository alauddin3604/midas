<?php

declare(strict_types=1);

namespace Database\Factories;

use Domain\Audit\Models\AuditEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditEntry>
 */
class AuditEntryFactory extends Factory
{
    protected $model = AuditEntry::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event' => 'identity.user.updated',
            'actor_id' => fake()->uuid(),
            'subject_type' => 'user',
            'subject_id' => fake()->uuid(),
            'changes' => null,
            'context' => null,
            'occurred_at' => now(),
        ];
    }
}
