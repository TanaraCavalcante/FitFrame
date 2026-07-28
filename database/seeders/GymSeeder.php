<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\Domain;
use App\Models\Gym;
use App\Models\GymClass;
use App\Models\GymSection;
use App\Models\PersonalTrainer;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\Testimonial;
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
                    'email' => 'info@pulse.gym',
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
                'plans' => [
                    [
                        'name' => 'Base',
                        'price' => 29.90,
                        'highlighted' => false,
                        'features' => [
                            'Accesso libero in sala pesi',
                            '1 lezione di gruppo a settimana',
                            'Scheda di allenamento base',
                        ],
                    ],
                    [
                        'name' => 'Pro',
                        'price' => 49.90,
                        'highlighted' => true,
                        'features' => [
                            'Accesso illimitato a tutte le lezioni',
                            'Scheda di allenamento personalizzata',
                            '1 sessione con personal trainer al mese',
                            'Accesso all\'app di monitoraggio',
                        ],
                    ],
                    [
                        'name' => 'Elite',
                        'price' => 79.90,
                        'highlighted' => false,
                        'features' => [
                            'Tutto quello incluso nel piano Pro',
                            'Sessioni con personal trainer illimitate',
                            'Piano nutrizionale personalizzato',
                            'Accesso prioritario agli eventi Pulse',
                        ],
                    ],
                ],
                'team' => [
                    ['name' => 'Marco Ferrari', 'specialty' => 'Head Coach, Powerlifting'],
                    ['name' => 'Giulia Bianchi', 'specialty' => 'Coach Functional Training'],
                    ['name' => 'Luca Romano', 'specialty' => 'Coach HIIT & Conditioning'],
                    ['name' => 'Sara Conti', 'specialty' => 'Coach Mobilità & Recupero'],
                ],
                'testimonials' => [
                    ['author_name' => 'Alessandro Greco', 'member_since' => 'Allievo da 8 mesi', 'text' => 'Sono entrato senza mai aver messo piede in palestra, oggi non salto un allenamento. I coach seguono davvero ogni progresso.'],
                    ['author_name' => 'Francesca Moretti', 'member_since' => 'Allieva da 1 anno', 'text' => 'Il piano Pro vale ogni euro. Ho perso 12 kg e trovato una community che mi spinge ogni giorno.'],
                    ['author_name' => 'Davide Rinaldi', 'member_since' => 'Allievo da 4 mesi', 'text' => 'Powerlifting con Marco è cambiato tutto. Tecnica curata nei minimi dettagli, risultati concreti fin dal primo mese.'],
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

            foreach (GymSection::DEFAULT_ORDER as $order => $section) {
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

            foreach ($data['plans'] ?? [] as $order => $planData) {
                $plan = Plan::create([
                    'gym_id' => $gym->id,
                    'name' => $planData['name'],
                    'price' => $planData['price'],
                    'highlighted' => $planData['highlighted'],
                    'order' => $order,
                ]);

                foreach ($planData['features'] as $featureOrder => $description) {
                    PlanFeature::create([
                        'plan_id' => $plan->id,
                        'description' => $description,
                        'order' => $featureOrder,
                    ]);
                }
            }

            foreach ($data['team'] ?? [] as $order => $trainer) {
                PersonalTrainer::create([
                    'gym_id' => $gym->id,
                    'name' => $trainer['name'],
                    'specialty' => $trainer['specialty'],
                    'order' => $order,
                ]);
            }

            foreach ($data['testimonials'] ?? [] as $order => $testimonial) {
                Testimonial::create([
                    'gym_id' => $gym->id,
                    'author_name' => $testimonial['author_name'],
                    'text' => $testimonial['text'],
                    'member_since' => $testimonial['member_since'],
                    'order' => $order,
                ]);
            }
        }
    }
}
