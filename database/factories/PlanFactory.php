<?php

namespace Database\Factories;

use App\Models\Gym;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
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
            'name' => fake()->randomElement(['Básico', 'Padrão', 'Premium']),
            'price' => fake()->randomFloat(2, 79, 299),
            'highlighted' => false,
            'order' => fake()->numberBetween(0, 5),
        ];
    }
}
