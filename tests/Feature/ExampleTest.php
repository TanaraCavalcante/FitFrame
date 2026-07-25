<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\Gym;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use LazilyRefreshDatabase;

    /**
     * A basic test example.
     *
     * L'host di default (APP_URL) è ora quello dell'area di gestione, quindi
     * qui si simula esplicitamente un dominio pubblico di una palestra per
     * verificare che il sito pubblico risponda correttamente.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $gym = Gym::factory()->create(['slug' => 'pulse']);
        Domain::factory()->for($gym)->create(['domain' => 'esempio.test']);

        $response = $this->get('http://esempio.test/');

        $response->assertStatus(200);
    }
}
