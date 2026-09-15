<?php

// Test generati da tanas:test

namespace Tests\Feature\Backend;

use App\Models\Gym;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ChatControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guests_cannot_ask_a_question(): void
    {
        $response = $this->postJson('http://gestione.fitframe.test/chat', [
            'domanda' => 'Come aggiungo un prodotto?',
        ]);

        $response->assertUnauthorized();
    }

    public function test_an_authenticated_user_can_ask_a_question_and_receive_an_answer(): void
    {
        Http::fake(['*/ask' => Http::response(['risposta' => 'Vai nella sezione Prodotti.'], 200)]);
        $gym = Gym::factory()->create();
        $user = User::factory()->for($gym)->create();

        $response = $this->actingAs($user)->postJson('http://gestione.fitframe.test/chat', [
            'domanda' => 'Come aggiungo un prodotto?',
        ]);

        $response->assertOk();
        $response->assertJson(['risposta' => 'Vai nella sezione Prodotti.']);
        $this->assertDatabaseHas('chat_messages', [
            'user_id' => $user->id,
            'question' => 'Come aggiungo un prodotto?',
            'answer' => 'Vai nella sezione Prodotti.',
        ]);
    }

    public function test_an_empty_question_is_rejected(): void
    {
        $gym = Gym::factory()->create();
        $user = User::factory()->for($gym)->create();

        $response = $this->actingAs($user)->postJson('http://gestione.fitframe.test/chat', [
            'domanda' => '',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('chat_messages', 0);
    }

    public function test_a_question_receives_a_controlled_fallback_when_the_rag_service_is_unavailable(): void
    {
        Http::fake(['*/ask' => Http::response(['error' => 'Assistente temporaneamente non disponibile'], 503)]);
        $gym = Gym::factory()->create();
        $user = User::factory()->for($gym)->create();

        $response = $this->actingAs($user)->postJson('http://gestione.fitframe.test/chat', [
            'domanda' => 'Come aggiungo un prodotto?',
        ]);

        $response->assertStatus(503);
        $response->assertJsonStructure(['error']);
        $this->assertDatabaseCount('chat_messages', 0);
    }

    public function test_repeated_questions_are_rate_limited(): void
    {
        Http::fake(['*/ask' => Http::response(['risposta' => 'ok'], 200)]);
        $gym = Gym::factory()->create();
        $user = User::factory()->for($gym)->create();

        for ($i = 0; $i < 10; $i++) {
            $this->actingAs($user)->postJson('http://gestione.fitframe.test/chat', [
                'domanda' => "Domanda numero {$i}",
            ])->assertOk();
        }

        $this->actingAs($user)->postJson('http://gestione.fitframe.test/chat', [
            'domanda' => 'Domanda numero 11',
        ])->assertStatus(429);
    }
}
