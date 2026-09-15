<?php

namespace App\Services\Chat;

use App\Exceptions\RagServiceUnavailableException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class RagServiceClient
{
    /**
     * Invia la domanda al servizio RAG (fitframe-rag) e ritorna la risposta.
     *
     * @throws RagServiceUnavailableException
     */
    public function ask(string $domanda): string
    {
        try {
            $response = Http::withToken(config('services.rag.token'))
                ->post(config('services.rag.url').'/ask', ['domanda' => $domanda]);
        } catch (ConnectionException $e) {
            throw new RagServiceUnavailableException('Il servizio RAG non è raggiungibile.', previous: $e);
        }

        if ($response->failed()) {
            throw new RagServiceUnavailableException('Il servizio RAG ha risposto con un errore.');
        }

        return $response->json('risposta');
    }
}
