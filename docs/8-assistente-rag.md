# 8. Assistente di aiuto (RAG)

## Cos'è

Nel gestionale (pannello admin) c'è un **pulsante di chat flottante**. Un
utente autenticato può scrivere una domanda in italiano — *"Come aggiungo
un corso?"*, *"Come recupero la password?"* — e ricevere una risposta
generata da un LLM a partire da una **documentazione scritta a mano**.

Questa tecnica si chiama **RAG** (*Retrieval-Augmented Generation*):
invece di lasciare che il modello risponda "a memoria" (e magari
inventi), prima si **cerca** nella documentazione il testo più
pertinente alla domanda, poi si chiede al modello di rispondere **solo**
usando quel testo.

## Idea chiave: due progetti, un solo assistente

L'assistente è diviso in **due repository separati**. Capire questa
divisione è la cosa più importante di tutta la doc.

| Repository | Linguaggio | Cosa fa |
|---|---|---|
| **FitFrame** *(questo)* | PHP / Laravel | Chi può chiedere (login), quante domande (limite), salvare la cronologia, mostrare la chat |
| [**fitframe-rag**](https://github.com/TanaraCavalcante/fitframe-rag) | Python / Flask | Il "cervello": legge la documentazione, cerca il testo giusto, chiama Groq (l'LLM) e scrive la risposta |

**FitFrame non contiene** documentazione dell'assistente, ricerca
semantica, embeddings né chiamate all'LLM. Manda una domanda a
`fitframe-rag` via HTTP e ne riceve la risposta. Se cambi *cosa sa
l'assistente*, lavori in `fitframe-rag`; se cambi *come si presenta o chi
può usarlo*, lavori qui.

```
 Browser (utente loggato)
   │  1. scrive la domanda nel widget
   ▼
 FitFrame (Laravel)
   │  2. controlla login + limite di 10 domande/minuto
   │  3. RagServiceClient → POST /ask  { "domanda": "..." }
   │                        Authorization: Bearer <token>
   ▼
 fitframe-rag (Python)
   │  4. cerca i 4 testi più simili nella knowledge base (FAISS)
   │  5. manda testi + domanda a Groq (openai/gpt-oss-120b)
   │  6. risponde { "risposta": "..." }
   ▼
 FitFrame
   │  7. salva domanda+risposta in chat_messages
   ▼
 Browser: mostra la risposta
```

## Cosa c'è in questo progetto

| Livello | File | Ruolo |
|---|---|---|
| Rotte | `routes/backend.php` | `POST chat` e `GET chat/history`, dentro il gruppo `auth` |
| Controller | `app/Http/Controllers/Backend/ChatController.php` | `ask` (fa la domanda) e `history` (carica lo storico) |
| Client HTTP | `app/Services/Chat/RagServiceClient.php` | Unico punto che parla con `fitframe-rag` |
| Eccezione | `app/Exceptions/RagServiceUnavailableException.php` | Segnala "servizio non disponibile" al controller |
| Modello | `app/Models/ChatMessage.php` | Una riga = una coppia domanda/risposta |
| Relazione | `User::chatMessages()` | `hasMany` verso `ChatMessage` |
| Migration | `database/migrations/2026_09_15_082719_create_chat_messages_table.php` | Crea `chat_messages` |
| Factory | `database/factories/ChatMessageFactory.php` | Per i test |
| Rate limiter + view composer | `app/Providers/AppServiceProvider.php` | Limiter `chat` e cronologia passata al widget |
| Config | `config/services.php` (chiave `rag`) + `.env` | URL e token del servizio |
| View | `resources/views/backend/layouts/components/chat-widget.blade.php` | HTML del widget |
| JavaScript | `public/js/backend-chat.js` | Comportamento del widget (JS puro, nessun framework) |
| CSS | `public/css/backend/chat-widget.css` | Stile, riusa i token di `variables.css` |
| Layout | `resources/views/backend/layouts/app.blade.php` | Include widget, JS, CSS e il meta tag `csrf-token` |
| Test | `tests/Feature/Backend/ChatControllerTest.php`, `tests/Unit/Chat/RagServiceClientTest.php` | Copertura di controller e client |

Il widget compare **solo nelle pagine autenticate** del gestionale: la
pagina di login usa un layout separato che non lo include.

## Come funziona, passo per passo

### 1. Fare una domanda — `POST chat`

Rotta `backend.chat`, in `routes/backend.php`, con middleware `auth` e
`throttle:chat`. Tutti gli utenti autenticati possono usarla, senza
distinzione di ruolo (`super_admin` e `gym_admin`).

`ChatController@ask`:

1. Valida il campo `domanda` (obbligatorio, stringa).
2. Chiama `RagServiceClient::ask($domanda)`.
3. Se il servizio non è disponibile → risponde `503` con
   `{"error": "Assistente temporaneamente non disponibile"}` e **non salva
   nulla**.
4. Se va bene → crea un `ChatMessage` (`user_id`, `question`, `answer`) e
   risponde `200` con `{"risposta": "..."}`.

### 2. Parlare con il servizio Python — `RagServiceClient`

```php
Http::withToken(config('services.rag.token'))
    ->post(config('services.rag.url').'/ask', ['domanda' => $domanda]);
```

- Manda l'header `Authorization: Bearer <RAG_SERVICE_TOKEN>`.
- Se non riesce a connettersi (servizio spento) → lancia
  `RagServiceUnavailableException`.
- Se il servizio risponde con qualsiasi errore (`401`, `400`, `503`…) →
  lancia la stessa eccezione.
- Altrimenti ritorna il testo di `risposta`.

Il controller non distingue *perché* il servizio non è disponibile: per
l'utente è sempre lo stesso messaggio. Il dettaglio si vede nei log di
`fitframe-rag`.

### 3. Cronologia — persistenza e visualizzazione

**Tabella `chat_messages`**

| Colonna | Tipo | Note |
|---|---|---|
| `id` | bigint | |
| `user_id` | FK → `users` | `cascadeOnDelete`: cancellando l'utente si cancella la sua cronologia |
| `question` | text | |
| `answer` | text | |
| `created_at`, `updated_at` | timestamp | |

Non c'è `gym_id`: il gestionale è unico per tutte le strutture e la
cronologia appartiene **all'utente**, non alla palestra. Ogni utente vede
solo i propri messaggi.

**Ultimi 20 messaggi (al caricamento della pagina).** Un *view composer*
in `AppServiceProvider` collega alla view del widget
`$chatHistory` (ultimi 20 messaggi dell'utente, in ordine cronologico) e
`$chatHistoryHasMore` (ci sono messaggi più vecchi?). La cronologia è
quindi già nell'HTML iniziale, senza chiamate aggiuntive: è la ragione per
cui la conversazione "sopravvive" al reload della pagina.

**Storico più vecchio — `GET chat/history`.** Il pulsante *"Carica
cronologia precedente"* chiama `backend.chat.history` con `before_id`
(obbligatorio: l'`id` del messaggio più vecchio già visibile). Il
controller ritorna fino a 20 messaggi con `id` minore, dello **stesso
utente**, in ordine cronologico, più `has_more` (`true` se ne restano
altri). Il widget li inserisce sopra quelli visibili e mantiene la
posizione di scroll.

### 4. Limite di richieste

In `AppServiceProvider` il limiter `chat` consente **10 domande al minuto
per utente** (`Limit::perMinute(10)->by($request->user()->id)`). Serve a
contenere i costi verso il servizio e verso Groq. Superato il limite,
Laravel risponde `429`. `GET chat/history` **non** ha limite: è solo
lettura di dati già presenti nel database.

### 5. Il widget (frontend)

`backend-chat.js` è JavaScript "vanilla" (coerente con il resto del
gestionale: nessuna SPA, nessun bundler). In sintesi:

- apre/chiude il pannello;
- all'invio mostra subito la domanda, disattiva il campo e mostra i tre
  puntini "sta scrivendo";
- fa `fetch` `POST` a `data-chat-url` con l'header `X-CSRF-TOKEN` (letto
  dal meta tag `csrf-token`) e il body `{"domanda": "..."}`;
- mostra la risposta, oppure un messaggio di errore se la chiamata fallisce
  (per qualunque errore, incluso `429`, l'utente vede "Assistente
  temporaneamente non disponibile");
- per la cronologia usa `data-history-url` e `data-oldest-message-id`,
  entrambi scritti dalla view sull'elemento `#chat-widget`.

Il testo dei messaggi viene inserito con `textContent` (JS) e `{{ }}`
(Blade): niente HTML interpretato, quindi nessun rischio di iniettare
codice attraverso una domanda o una risposta.

## Configurazione

In `.env` (letta da `config/services.php`, chiave `services.rag`):

```
RAG_SERVICE_URL=http://127.0.0.1:5002
RAG_SERVICE_TOKEN=
```

| Variabile | Significato |
|---|---|
| `RAG_SERVICE_URL` | Indirizzo di `fitframe-rag`. Default: `http://127.0.0.1:5002` |
| `RAG_SERVICE_TOKEN` | Segreto condiviso. Deve essere **identico** a `API_TOKEN` nel `.env` di `fitframe-rag`. Se manca o è diverso, il servizio risponde `401` e l'utente vede l'errore generico |

## Cosa fa `fitframe-rag` (in breve)

Serve solo a capire cosa arriva dall'altra parte; i dettagli sono nel suo
README.

- All'avvio legge tutti i file `knowledge_base/*.md` (uno per argomento),
  li divide in pezzi (*chunk*) e li trasforma in vettori con un modello
  locale, indicizzandoli in FAISS. L'indice vive in memoria: **per
  applicare una modifica alla documentazione bisogna riavviare il
  servizio**.
- `POST /ask` verifica il token, cerca i 4 chunk più simili alla domanda,
  li manda a Groq con un prompt che impone di rispondere in italiano e solo
  in base al contesto, e restituisce `{"risposta": "..."}`.
- Se la risposta non è nella documentazione, l'assistente lo dice e
  rimanda all'assistenza tecnica invece di inventare.
- Errori: `401` token errato, `400` domanda vuota, `503` errore interno
  (es. Groq non raggiungibile). `GET /health` (pubblico) ritorna
  `{"status": "ok", "chunks": N}`.

## Avvio in locale

1. Clona `fitframe-rag`, crea il venv e installa le dipendenze (vedi il suo
   README), poi compila il suo `.env` con `GROQ_API_KEY` e `API_TOKEN`.
2. Copia lo stesso valore di `API_TOKEN` in `RAG_SERVICE_TOKEN` nel `.env`
   di FitFrame.
3. Avvia il servizio con `python api.py` (ascolta su
   `http://127.0.0.1:5002`). **Non parte con `composer dev`**: va lanciato
   a mano.
4. Verifica: `http://127.0.0.1:5002/health` deve rispondere
   `{"status": "ok", ...}`.
5. Assicurati di aver eseguito `php artisan migrate` (serve la tabella
   `chat_messages`).

## Test

```bash
php artisan test --compact tests/Feature/Backend/ChatControllerTest.php
php artisan test --compact tests/Unit/Chat/RagServiceClientTest.php
```

I test **non richiedono** `fitframe-rag` acceso: le chiamate HTTP sono
simulate con `Http::fake()`. Coprono: accesso negato agli ospiti,
domanda valida/vuota, fallback quando il servizio non risponde, limite di
10 richieste al minuto, e per lo storico: `before_id` obbligatorio, ordine
cronologico, `has_more` e isolamento tra utenti (un utente non può leggere
i messaggi di un altro).

## Problemi comuni

| Sintomo | Causa probabile |
|---|---|
| Il widget dice sempre "Assistente temporaneamente non disponibile" | `fitframe-rag` non è avviato; oppure `RAG_SERVICE_TOKEN` ≠ `API_TOKEN` (`401`); oppure `GROQ_API_KEY` non valida/servizio Groq non raggiungibile (`503`). Controlla `/health` e i log di `fitframe-rag` |
| Errore SQL / "no such table: chat_messages" | Migration non eseguita: `php artisan migrate` |
| Ho aggiunto un articolo alla knowledge base ma l'assistente non lo conosce | `fitframe-rag` va riavviato per ricostruire l'indice |
| Dopo molte domande ravvicinate il widget smette di rispondere | Limite di 10 domande al minuto per utente (`429`); riprova dopo qualche secondo |
| Le modifiche a `backend-chat.js` o al CSS non si vedono | Cache del browser: ricarica forzata (`Ctrl+F5`). Il JS ha già il cache-busting `?v=filemtime` |

## Limiti noti e scelte da ricordare

- **Nessuna memoria di conversazione lato LLM.** Ogni domanda è
  indipendente: a `fitframe-rag` arriva solo la domanda corrente, non le
  precedenti. La cronologia serve all'utente per rileggere, non al modello
  per ricordare.
- **`chat_messages` appartiene solo a Laravel.** Il servizio Python è
  *stateless* e non deve accedere a questo database (rischio di lock su
  SQLite e accoppiamento nascosto tra due repository). Se un giorno gli
  servisse lo storico, la strada è un endpoint Laravel dedicato, non
  l'accesso diretto al DB.
- **Nessuna lunghezza massima sulla `domanda`**: la validazione richiede
  solo "obbligatoria e stringa".
- **Nessun timeout esplicito** su `Http::post`: vale il default di Laravel
  (30 secondi).
- **Nessun supervisor sul processo Python**: se cade, va riavviato a mano
  (accettabile in sviluppo, da rivalutare in produzione).

## Dove cambiare cosa

| Voglio… | Dove |
|---|---|
| Aggiungere o correggere una risposta dell'assistente | `fitframe-rag/knowledge_base/*.md`, poi riavviare il servizio |
| Cambiare il tono o le regole delle risposte | `SYSTEM_PROMPT` in `fitframe-rag/api.py` |
| Cambiare il limite di domande | `RateLimiter::for('chat', …)` in `AppServiceProvider` |
| Cambiare aspetto o testi del widget | `chat-widget.blade.php`, `chat-widget.css` |
| Cambiare quanti messaggi si caricano per volta | `->take(20)` nel view composer e `->limit(20)` in `ChatController@history` (tenerli allineati) |
| Cambiare indirizzo o token del servizio | `.env` (`RAG_SERVICE_URL`, `RAG_SERVICE_TOKEN`) |

## Riferimenti

- Piano di implementazione e decisioni: [plan-chatbot-rag-gestionale.md](superpowers/plans/plan-chatbot-rag-gestionale.md)
- Repository del servizio: <https://github.com/TanaraCavalcante/fitframe-rag>
