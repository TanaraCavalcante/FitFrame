<?php

// Test generati da tanas:test

namespace Tests\Unit\Chat;

use App\Exceptions\RagServiceUnavailableException;
use App\Services\Chat\RagServiceClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RagServiceClientTest extends TestCase
{
    public function test_ask_returns_the_answer_from_a_successful_response(): void
    {
        config(['services.rag.url' => 'http://rag.test', 'services.rag.token' => 'secret-token']);
        Http::fake(['rag.test/*' => Http::response(['risposta' => 'Vai nella sezione Prodotti.'], 200)]);

        $risposta = (new RagServiceClient)->ask('Come aggiungo un prodotto?');

        $this->assertSame('Vai nella sezione Prodotti.', $risposta);
    }

    public function test_ask_sends_the_question_to_the_configured_endpoint(): void
    {
        config(['services.rag.url' => 'http://rag.test', 'services.rag.token' => 'secret-token']);
        Http::fake(['rag.test/*' => Http::response(['risposta' => 'ok'], 200)]);

        (new RagServiceClient)->ask('Come cambio il tema del sito?');

        Http::assertSent(fn ($request) => $request->url() === 'http://rag.test/ask'
            && $request['domanda'] === 'Come cambio il tema del sito?');
    }

    public function test_ask_sends_the_configured_token_as_a_bearer_authorization_header(): void
    {
        config(['services.rag.url' => 'http://rag.test', 'services.rag.token' => 'secret-token']);
        Http::fake(['rag.test/*' => Http::response(['risposta' => 'ok'], 200)]);

        (new RagServiceClient)->ask('Come cambio il tema del sito?');

        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer secret-token'));
    }

    public function test_ask_throws_when_the_service_responds_with_an_error_status(): void
    {
        config(['services.rag.url' => 'http://rag.test', 'services.rag.token' => 'secret-token']);
        Http::fake(['rag.test/*' => Http::response(['error' => 'Assistente temporaneamente non disponibile'], 503)]);

        $this->expectException(RagServiceUnavailableException::class);

        (new RagServiceClient)->ask('Come cambio il tema del sito?');
    }

    public function test_ask_throws_when_the_service_is_unreachable(): void
    {
        config(['services.rag.url' => 'http://rag.test', 'services.rag.token' => 'secret-token']);
        Http::fake(function () {
            throw new ConnectionException('Connection refused');
        });

        $this->expectException(RagServiceUnavailableException::class);

        (new RagServiceClient)->ask('Come cambio il tema del sito?');
    }
}
