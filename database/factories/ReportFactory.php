<?php

namespace Database\Factories;

use App\Enums\ReportReason;
use App\Models\Event;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Report>
 */
class ReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'attendee_id' => User::factory(),
            'event_id' => Event::factory(),
            'reason' => fake()->randomElement(ReportReason::cases()),
        ];
    }
}
