<?php

namespace Database\Factories;

use App\Models\Gym;
use App\Models\PersonalTrainer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PersonalTrainer>
 */
class PersonalTrainerFactory extends Factory
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
            'name' => fake()->name(),
            'specialty' => fake()->randomElement(['Musculação', 'Funcional', 'Yoga', 'Crossfit']),
            'photo_path' => 'base/team/placeholder.jpg',
            'order' => fake()->numberBetween(0, 10),
        ];
    }
}
