# Changelog - feature/rag

Tutte le modifiche rilevanti al branch `feature/rag` sono documentate in questo file.

Il formato è basato su [Keep a Changelog](https://keepachangelog.com/it/1.1.0/).

## [2026-09-15] - Widget frontend del chatbot (Fase 5)

### Aggiunto
- Mockup del widget (canvas con 3 stati: chiuso, aperto chiaro, aperto scuro) presentato e approvato esplicitamente prima dell'implementazione, come richiesto dal checkpoint di design del piano.
- Blade component `backend.layouts.components.chat-widget`: pulsante flottante (FAB) + pannello di chat (header, cronologia messaggi, campo domanda), incluso in `app.blade.php` solo per le pagine autenticate.
- `public/css/backend/chat-widget.css`, aggiunto al loop di stylesheet di `app.blade.php` — riusa i token colore/ombra già esistenti in `variables.css` (light/dark).
- `public/js/backend-chat.js`: apertura/chiusura del pannello, invio della domanda via `fetch` (header `X-CSRF-TOKEN`), indicatore "sta scrivendo", rendering della risposta o di un messaggio di errore controllato in caso di servizio non disponibile.
- Meta tag `csrf-token` in `app.blade.php`, necessario per la chiamata `fetch` POST del widget.

## [2026-09-15] - Implementazione lato Laravel del chatbot RAG (Fasi 1-4)

### Aggiunto
- Migration `chat_messages` (`user_id`, `question`, `answer`) e modello `ChatMessage` con relazione `belongsTo(User::class)`.
- `App\Services\Chat\RagServiceClient`: chiama `POST /ask` sul servizio `fitframe-rag` con header `Authorization: Bearer` (token da `RAG_SERVICE_TOKEN`), lancia `App\Exceptions\RagServiceUnavailableException` su timeout/connessione fallita/risposta di errore.
- `App\Http\Controllers\Backend\ChatController@ask`: valida la domanda, chiama `RagServiceClient`, salva `ChatMessage`, ritorna la risposta in JSON o un fallback controllato (503) se il servizio RAG non è disponibile.
- Rotta `POST chat` (nome `backend.chat`) in `routes/backend.php`, dentro il gruppo `auth`, con `throttle:chat`.
- Limiter `chat` in `AppServiceProvider` (10 richieste al minuto per utente).
- Config `services.rag` (`url`, `token`) e variabili `RAG_SERVICE_URL`/`RAG_SERVICE_TOKEN` in `.env.example`.
- Test generati con `/tanas:test`: `RagServiceClientTest` (5), `ChatMessageTest` (1), `ChatControllerTest` (5) — 142/142 test verdi.

### Modificato
- `docs/plan-chatbot-rag-gestionale.md`: corretto il nome del progetto Python (`fitframe-rag`, non più `fitframe-help-rag`), corretta la rotta (`chat`, non `backend/chat` — nessun prefisso URI nelle rotte di questo file), aggiunta l'autenticazione tra i due servizi (chiave condivisa via header), aggiunta la nota sui confini del database (`chat_messages` di proprietà esclusiva di Laravel), Fasi 1-4 marcate come completate.

## [2026-09-10] - Revisione del piano: architettura a due componenti

### Modificato
- `docs/plan-chatbot-rag-gestionale.md`: rivista l'architettura da "tutto in Laravel" a due repository separati — `fitframe-help-rag` (nuovo progetto Python, non ancora creato, processo nativo venv) possiede l'intera pipeline RAG (base di conoscenza Markdown, chunking, embeddings locali, FAISS, chiamata a Groq per la risposta); FitFrame resta responsabile solo di autenticazione, rotta `POST backend/chat` con throttle, client HTTP verso il servizio Python, persistenza di `chat_messages` e widget. Verificato (analizzando il progetto locale `ai-chat`) che Groq copre la chat completion ma non gli embeddings, da cui la revisione. Aggiunte note esplicite su: checkpoint di conferma design prima della fase frontend, nessuna condivisione di file Markdown o di database tra i due repository (solo contratto HTTP).

## [2026-09-10] - Piano del chatbot RAG di aiuto contestuale

### Aggiunto
- `docs/plan-chatbot-rag-gestionale.md`: piano di implementazione del chatbot AI di aiuto contestuale nel gestionale — architettura RAG interamente in Laravel 12 (nessun servizio Python/Docker, nessun upgrade a Laravel 13), Groq come provider per chat completions ed embeddings, base di conoscenza in Markdown, ricerca per cosine similarity in PHP, cronologia per utente, widget di chat come Blade component.
