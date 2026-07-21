<?php

namespace Database\Factories;

use App\Models\Gym;
use App\Models\GymClass;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GymClass>
 */
class GymClassFactory extends Factory
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
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'icon' => 'fa-solid fa-dumbbell',
            'order' => fake()->numberBetween(0, 10),
        ];
    }
}
