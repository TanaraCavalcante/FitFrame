<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\Gym;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contact>
 */
class ContactFactory extends Factory
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
            'address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
            'whatsapp' => fake()->phoneNumber(),
            'instagram' => '@'.fake()->userName(),
            'hours' => 'Seg-Sex 06h-22h, Sáb 08h-14h',
        ];
    }
}
