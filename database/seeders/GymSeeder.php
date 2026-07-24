<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\Domain;
use App\Models\Gym;
use App\Models\GymClass;
use App\Models\GymSection;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GymSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Popola le 3 palestre con i dati strutturali: gym, domain, contact e ordine
     * delle sezioni. Il contenuto reale (classi, piani, testimonianze, ecc.)
     * viene popolato in seguito, quando le sezioni saranno costruite.
     */
    public function run(): void
    {
        $gyms = [
            [
                'name' => 'Pulse',
                'slug' => 'pulse',
                'domain' => 'pulse.test',
                'contact' => [
                    'address' => 'Via Roma, Torino',
                    'phone' => '+39 011 123 4567',
                    'whatsapp' => '+39 333 123 4567',
                    'instagram' => '@pulse.gym',
                    'hours' => 'Lun-Ven 06:00-22:00, Sab 08:00-14:00',
                ],
                // Contenuto reale (Pulse serve da base per gli altri temi, vedi docs/7-todo.md).
                'classes' => [
                    ['name' => 'HIIT', 'icon' => 'fa-solid fa-bolt', 'description' => 'Allenamento a intervalli ad alta intensità per bruciare al massimo in poco tempo.'],
                    ['name' => 'Functional Training', 'icon' => 'fa-solid fa-person-running', 'description' => 'Movimenti funzionali che allenano forza, mobilità e coordinazione insieme.'],
                    ['name' => 'Powerlifting', 'icon' => 'fa-solid fa-dumbbell', 'description' => 'Tecnica e forza pura nei tre grandi sollevamenti: squat, panca, stacco.'],
                    ['name' => 'Mobilità & Recupero', 'icon' => 'fa-solid fa-heart-pulse', 'description' => 'Lavoro di mobilità articolare per prevenire infortuni e migliorare le performance.'],
                ],
            ],
            [
                'name' => 'Zenflow',
                'slug' => 'zenflow',
                'domain' => 'zenflow.test',
                'contact' => [
                    'address' => 'Viale Leonardo da Vinci, Prato',
                    'phone' => '+39 0574 123 456',
                    'whatsapp' => '+39 333 234 5678',
                    'instagram' => '@zenflow.gym',
                    'hours' => 'Lun-Ven 07:00-21:00, Sab 09:00-13:00',
                ],
            ],
            [
                'name' => 'Iron House',
                'slug' => 'iron-house',
                'domain' => 'iron-house.test',
                'contact' => [
                    'address' => 'Via Torinese, Roma',
                    'phone' => '+39 06 123 4567',
                    'whatsapp' => '+39 333 345 6789',
                    'instagram' => '@ironhouse.gym',
                    'hours' => 'Lun-Ven 06:00-23:00, Sab-Dom 08:00-20:00',
                ],
            ],
        ];

        // Ordine di default delle sezioni riordinabili (vedi docs/6-temas.md sezione H).
        // "contact-cta" con trattino, per corrispondere al nome del file Blade.
        $defaultSectionOrder = ['classes', 'plans', 'gallery', 'team', 'testimonials', 'contact-cta'];

        foreach ($gyms as $data) {
            $gym = Gym::create([
                'name' => $data['name'],
                'slug' => $data['slug'],
            ]);

            Domain::create([
                'gym_id' => $gym->id,
                'domain' => $data['domain'],
            ]);

            Contact::create([
                'gym_id' => $gym->id,
                ...$data['contact'],
            ]);

            foreach ($defaultSectionOrder as $order => $section) {
                GymSection::create([
                    'gym_id' => $gym->id,
                    'section' => $section,
                    'order' => $order,
                ]);
            }

            foreach ($data['classes'] ?? [] as $order => $class) {
                GymClass::create([
                    'gym_id' => $gym->id,
                    'name' => $class['name'],
                    'description' => $class['description'],
                    'icon' => $class['icon'],
                    'order' => $order,
                ]);
            }
        }
    }
}
