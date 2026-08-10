<?php

namespace Database\Factories;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('+1 week', '+3 months');
        $end = (clone $start)->modify('+'.fake()->numberBetween(1, 6).' hours');

        return [
            'name' => rtrim(fake()->sentence(4), '.'),
            'description' => fake()->paragraphs(3, true),
            'location' => fake()->streetAddress().', '.fake()->city(),
            'start_date_time' => $start,
            'end_date_time' => $end,
            'tiket_cost' => fake()->randomElement([0, 0, 15, 25, 49.99, 99]),
            'max_capacity' => fake()->randomElement([null, 50, 100, 200]),
            'is_active' => true,
            'category_id' => Category::factory(),
            'admin_id' => Admin::factory(),
        ];
    }

    /**
     * Indicate that the event is unpublished.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }

    /**
     * Indicate that the event has already taken place.
     */
    public function past(): static
    {
        return $this->state(function (array $attributes) {
            $start = fake()->dateTimeBetween('-3 months', '-1 week');

            return [
                'start_date_time' => $start,
                'end_date_time' => (clone $start)->modify('+2 hours'),
            ];
        });
    }
}
