<?php

namespace Database\Factories;

use App\Models\Gym;
use App\Models\GymSection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GymSection>
 */
class GymSectionFactory extends Factory
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
            'section' => fake()->randomElement([
                'classes', 'plans', 'gallery', 'team', 'testimonials', 'contact_cta',
            ]),
            'order' => fake()->numberBetween(0, 5),
        ];
    }
}
