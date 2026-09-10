# Piano: Chatbot RAG di aiuto contestuale (gestionale)

Data: 2026-09-10
Stato: IN REVISIONE

## Obiettivo

Gli utenti autenticati del gestionale (pannello admin, dominio `ADMIN_DOMAIN`)
possono fare domande in linguaggio naturale su come usare le funzionalità del
pannello (es. "Come aggiungo un prodotto?", "Come cambio il tema del sito?",
"Dove trovo le impostazioni utente?") e ricevere una risposta contestuale,
generata da un LLM a partire da una base di conoscenza scritta a mano e
recuperata tramite ricerca semantica (RAG).

## Approccio tecnico

- Tutto integrato in Laravel 12, nessun servizio esterno (Python/Docker
  scartato), nessun upgrade di framework (Laravel 13 + `laravel/ai` valutato
  e scartato: upgrade major non giustificato, e la ricerca vettoriale nativa
  di quel pacchetto richiede comunque Postgres/MariaDB/MongoDB, non SQLite).
- Provider LLM: **Groq** (`api.groq.com`, chiave già disponibile
  sull'account dell'utente), sia per chat completions sia per embeddings.
- Base di conoscenza: file Markdown scritti a mano e versionati in git
  (`resources/help/*.md`).
- Un comando Artisan legge i file, li spezza in chunk, chiama l'API
  embeddings di Groq per ogni chunk, salva testo + vettore in tabella
  `help_chunks`.
- Endpoint Laravel riceve la domanda dell'utente autenticato, genera
  l'embedding della domanda via Groq, calcola cosine similarity in PHP
  puro contro i chunk salvati, prende i top-N più rilevanti, costruisce un
  prompt con quel contesto + la domanda, chiama la chat completion di
  Groq, ritorna la risposta.
- Cronologia delle conversazioni salvata per utente (tabella
  `chat_messages`).
- Rate limiting sulla rotta (`throttle:chat`), stesso pattern già usato per
  login/password-reset in `routes/backend.php`.
- Widget di chat: Blade component incluso nel layout unico del gestionale
  (`app.blade.php`), floating button + pannello, JS vanilla (nessun build
  step, coerente con l'assenza di Vite/npm nel backend attuale).
- Lingua delle risposte: italiano, coerente con `APP_LOCALE=it` e il resto
  del gestionale.
- Accesso: tutti gli utenti autenticati del gestionale (nessuna
  restrizione per ruolo).

## Attività

### Fase 1 — Database e modelli
- [ ] Migration `help_chunks` (id, source_file, chunk_text, embedding
      json, order, timestamps)
- [ ] Migration `chat_messages` (id, user_id FK, question, answer,
      timestamps)
- [ ] Modello `HelpChunk` (cast `embedding` come array)
- [ ] Modello `ChatMessage` (`belongsTo(User::class)`)

### Fase 2 — Servizi e comando di indicizzazione
- [ ] Service (es. `App\Services\Chat\GroqClient`) che incapsula le
      chiamate HTTP a Groq (chat completions + embeddings), legge la
      chiave API da config/env (`GROQ_API_KEY`)
- [ ] Service (es. `App\Services\Chat\KnowledgeBaseSearch`) che calcola
      cosine similarity sui `HelpChunk` e ritorna i top-N più rilevanti
- [ ] Comando Artisan `help:index` che legge i `.md` da
      `resources/help/`, fa chunking, chiama gli embeddings, salva/
      aggiorna `help_chunks` in modo idempotente (per `source_file`)
- [ ] File Markdown iniziali in `resources/help/` — contenuto scritto
      dall'utente (fuori dallo scope implementativo di questo piano)

### Fase 3 — Controller e rotte
- [ ] `App\Http\Controllers\Backend\ChatController@ask` (POST): riceve
      la domanda, chiama `KnowledgeBaseSearch` + `GroqClient`, salva
      `ChatMessage`, ritorna JSON
- [ ] Rotta in `routes/backend.php`, dentro il gruppo `auth`, con
      `throttle:chat`
- [ ] Definire il limiter `chat` (es. in `AppServiceProvider` o dove
      sono già definiti gli altri limiter del progetto)

### Fase 4 — Test
- [ ] Test feature per l'endpoint chat (richiede autenticazione, 200 con
      mock del `GroqClient`, throttle rispettato) — usare `/tanas:test`
      per generare gli stub
- [ ] Test unit per `KnowledgeBaseSearch` (cosine similarity, ordinamento
      top-N)
- [ ] Test per il comando `help:index` (chunking, idempotenza
      sull'aggiornamento)

### Fase 5 — Frontend (widget)
- [ ] Blade component `backend.layouts.components.chat-widget` (floating
      button + pannello, markup Bootstrap coerente con lo stile esistente
      — es. classi `bg-popover`, `rounded-3` già usate nell'aside)
- [ ] JS vanilla (es. `public/js/backend-chat.js`, incluso in
      `app.blade.php` accanto a `backend.js`, stesso pattern di
      cache-busting con `filemtime()`)
- [ ] Include del component in `resources/views/backend/layouts/app.blade.php`

## Migration necessarie

| Tabella | Colonne principali | Note |
|---|---|---|
| help_chunks | id, source_file, chunk_text, embedding (json), order, timestamps | embedding salvato come array JSON di float |
| chat_messages | id, user_id (FK users), question, answer, timestamps | cronologia per utente; nessun `gym_id` — il gestionale è unico per tutte le palestre |

## Rotte da aggiungere

| Metodo | URI | Controller/Azione | Note |
|---|---|---|---|
| POST | backend/chat | ChatController@ask | dentro `Route::domain(admin_domain)`, middleware `auth` + `throttle:chat` |

## Rischi e note

- L'endpoint embeddings di Groq (modello `nomic-embed-text-v1_5` secondo
  fonti terze) è confermato solo empiricamente dall'utente (chiave attiva
  su console.groq.com) — non risultava nella documentazione ufficiale dei
  modelli consultata durante la pianificazione. Verificare il nome esatto
  del modello/endpoint a inizio Fase 2, prima di scrivere il comando di
  indicizzazione.
- Cosine similarity in PHP puro su tutti i chunk ad ogni richiesta è
  accettabile solo per una base di conoscenza piccola (decine/poche
  centinaia di chunk). Se il manuale del gestionale crescerà molto,
  rivalutare un vector store dedicato — fuori scope per questa fase.
- Ogni domanda genera almeno 2 chiamate a Groq (embedding della domanda +
  risposta finale): `throttle:chat` mitiga abuso, ma non è previsto un
  controllo di costo/budget mensile in questo piano.
- I contenuti dei file Markdown vanno scritti/mantenuti a mano
  dall'utente; questo piano copre solo l'infrastruttura (indicizzazione,
  ricerca, endpoint, widget), non la stesura dei testi di aiuto.
- Decisione architetturale scartata ma discussa: servizio Python +
  Docker separato (valore di apprendimento per l'utente) e upgrade a
  Laravel 13 + `laravel/ai` — entrambi documentati sopra per contesto, nel
  caso vadano riconsiderati in futuro.
