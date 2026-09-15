<?php

// Test generati da tanas:test

namespace Tests\Feature\Backend;

use App\Models\ChatMessage;
use App\Models\Gym;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ChatWidgetHistoryTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_previous_chat_messages_are_rendered_in_the_widget(): void
    {
        $gym = Gym::factory()->create();
        $user = User::factory()->for($gym)->create();
        ChatMessage::factory()->for($user)->create([
            'question' => 'Come aggiungo un prodotto?',
            'answer' => 'Vai nella sezione Prodotti.',
        ]);

        $response = $this->actingAs($user)->get('http://gestione.fitframe.test/dashboard');

        $response->assertOk();
        $response->assertSee('Come aggiungo un prodotto?');
        $response->assertSee('Vai nella sezione Prodotti.');
    }

    public function test_the_default_greeting_is_shown_when_there_is_no_history(): void
    {
        $gym = Gym::factory()->create();
        $user = User::factory()->for($gym)->create();

        $response = $this->actingAs($user)->get('http://gestione.fitframe.test/dashboard');

        $response->assertOk();
        $response->assertSee("Sono l'assistente del gestionale FitFrame", false);
    }

    public function test_a_users_chat_history_does_not_leak_to_another_user(): void
    {
        $gym = Gym::factory()->create();
        $owner = User::factory()->for($gym)->create();
        $otherUser = User::factory()->for($gym)->create();
        ChatMessage::factory()->for($owner)->create([
            'question' => 'Domanda privata di un altro utente',
            'answer' => 'Risposta privata di un altro utente',
        ]);

        $response = $this->actingAs($otherUser)->get('http://gestione.fitframe.test/dashboard');

        $response->assertOk();
        $response->assertDontSee('Domanda privata di un altro utente');
        $response->assertDontSee('Risposta privata di un altro utente');
    }
}
