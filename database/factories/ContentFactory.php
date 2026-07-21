<?php

namespace Database\Factories;

use App\Models\Content;
use App\Models\Gym;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Content>
 */
class ContentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'gym_id' => Gym::factory(),
            'key' => fake()->unique()->word(),
            'value' => fake()->sentence(),
        ];
    }
}
