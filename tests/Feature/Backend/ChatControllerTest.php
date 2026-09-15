<?php

// Test generati da tanas:test

namespace Tests\Feature\Backend;

use App\Models\ChatMessage;
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

    public function test_guests_cannot_view_chat_history(): void
    {
        $response = $this->getJson('http://gestione.fitframe.test/chat/history?before_id=1');

        $response->assertUnauthorized();
    }

    public function test_before_id_is_required_to_load_history(): void
    {
        $gym = Gym::factory()->create();
        $user = User::factory()->for($gym)->create();

        $response = $this->actingAs($user)->getJson('http://gestione.fitframe.test/chat/history');

        $response->assertStatus(422);
    }

    public function test_history_returns_messages_older_than_the_given_id_in_chronological_order(): void
    {
        $gym = Gym::factory()->create();
        $user = User::factory()->for($gym)->create();
        $oldest = ChatMessage::factory()->for($user)->create(['question' => 'Prima domanda']);
        $middle = ChatMessage::factory()->for($user)->create(['question' => 'Seconda domanda']);
        $newest = ChatMessage::factory()->for($user)->create(['question' => 'Terza domanda']);

        $response = $this->actingAs($user)->getJson("http://gestione.fitframe.test/chat/history?before_id={$newest->id}");

        $response->assertOk();
        $response->assertJson(['has_more' => false]);
        $response->assertJsonPath('messages.0.question', 'Prima domanda');
        $response->assertJsonPath('messages.1.question', 'Seconda domanda');
        $response->assertJsonCount(2, 'messages');
    }

    public function test_history_reports_has_more_when_older_messages_remain(): void
    {
        $gym = Gym::factory()->create();
        $user = User::factory()->for($gym)->create();
        ChatMessage::factory()->for($user)->count(41)->create();
        // La 20ª più recente: confine tra la pagina già mostrata (20 più recenti) e quelle da caricare.
        $before = $user->chatMessages()->latest('id')->skip(19)->first();

        $response = $this->actingAs($user)->getJson("http://gestione.fitframe.test/chat/history?before_id={$before->id}");

        $response->assertOk();
        $response->assertJson(['has_more' => true]);
        $response->assertJsonCount(20, 'messages');
    }

    public function test_history_does_not_return_another_users_messages(): void
    {
        $gym = Gym::factory()->create();
        $owner = User::factory()->for($gym)->create();
        $otherUser = User::factory()->for($gym)->create();
        ChatMessage::factory()->for($owner)->create(['question' => 'Domanda privata']);
        $marker = ChatMessage::factory()->for($otherUser)->create();

        $response = $this->actingAs($otherUser)->getJson("http://gestione.fitframe.test/chat/history?before_id={$marker->id}");

        $response->assertOk();
        $response->assertJsonCount(0, 'messages');
    }
}
