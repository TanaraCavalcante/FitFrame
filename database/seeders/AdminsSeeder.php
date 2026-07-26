<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Gym;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminsSeeder extends Seeder
{
    /**
     * Crea gli utenti di accesso al pannello admin: un super_admin
     * (nessuna struttura) e un gym_admin di prova legato a "pulse".
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Tanara',
            'surname' => 'Cavalcante',
            'email' => 'admin@fitframe.it',
            'password' => bcrypt('12345678'),
            'role' => UserRole::SuperAdmin,
            'gym_id' => null,
        ]);

        User::factory()->create([
            'name' => 'Admin',
            'surname' => 'Pulse',
            'email' => 'admin@pulse.it',
            'password' => bcrypt('12345678'),
            'role' => UserRole::GymAdmin,
            'gym_id' => Gym::where('slug', 'pulse')->firstOrFail()->id,
        ]);
    }
}
