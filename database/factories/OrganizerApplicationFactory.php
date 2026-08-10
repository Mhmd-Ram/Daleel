<?php

namespace Database\Factories;

use App\Enums\OrganizerApplicationStatus;
use App\Models\OrganizerApplication;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrganizerApplication>
 */
class OrganizerApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'status' => OrganizerApplicationStatus::Pending,
            'message' => fake()->paragraph(),
            'reviewed_by' => null,
            'reviewed_at' => null,
        ];
    }
}
