# Piano: Chatbot RAG di aiuto contestuale (gestionale)

Data: 2026-09-10
Stato: APPROVATO

## Obiettivo

Gli utenti autenticati del gestionale (pannello admin, dominio `ADMIN_DOMAIN`)
possono fare domande in linguaggio naturale su come usare le funzionalità del
pannello (es. "Come aggiungo un prodotto?", "Come cambio il tema del sito?",
"Dove trovo le impostazioni utente?") e ricevere una risposta contestuale,
generata da un LLM a partire da una base di conoscenza scritta a mano e
recuperata tramite ricerca semantica (RAG).

## Approccio tecnico

Architettura a **due componenti, in due repository separati**:

1. **`fitframe-rag`** — progetto Python, sibling di FitFrame
   (`C:\laragon\www\fitframe-rag`), **già creato e con la base
   implementata**. Pipeline RAG completa: legge i `.md` di
   `knowledge_base/`, chunking, embeddings locali (HuggingFace
   `sentence-transformers`, stesso modello già validato nel progetto
   `ai-chat`), indicizzazione FAISS, ricerca per similarità, costruzione
   del prompt, chiamata alla chat completion di **Groq** (modello
   `openai/gpt-oss-120b`, già usato con successo in `ai-chat`), ritorna
   la risposta. Gira come **processo nativo (venv)** su
   `http://127.0.0.1:5002`, non containerizzato. Espone:
   `POST /ask` (`{"domanda": "..."}` → `{"risposta": "..."}`, errori
   `400`/`503`) e `GET /health` (`{"status": "ok", "chunks": N}`). Codice
   adattato dal pattern già testato in `ai-chat/rag.py` e
   `ai-chat/api.py` — **non riutilizza l'istanza in esecuzione di
   `ai-chat`**: caso d'uso diverso (corpus fisso di documentazione vs
   upload di documenti per sessione). Ha un proprio piano di
   implementazione e una propria documentazione, nel proprio repository
   (`docs/plan-fitframe-rag.md`).
2. **FitFrame** (questo repository) — Laravel resta responsabile di:
   autenticazione (middleware `auth` già esistente), rotta
   `POST chat` con `throttle:chat`, un client HTTP che chiama
   l'endpoint del servizio Python, persistenza di
   `{user_id, question, answer}` in `chat_messages`, e il widget di chat
   (Blade + JS vanilla) nel layout del gestionale.

**Questo piano copre solo il lato Laravel** (connessione HTTP al servizio
esterno + persistenza + frontend). Nessuna logica di chunking, embedding,
FAISS o chiamata diretta a Groq vive in questo repository.

- Fonte dei contenuti di aiuto (file Markdown, chunking, indicizzazione):
  vive interamente in `fitframe-rag`, **non** in questo repository —
  Laravel non conosce percorsi di file né dettagli di indicizzazione,
  riceve solo `{ domanda }` e restituisce `{ risposta }`. Contratto
  minimo tra i due sistemi, nessuna dipendenza da un percorso di
  filesystem condiviso tra i due processi.
- **Autenticazione tra i due servizi**: chiave condivisa già configurata
  in entrambi gli `.env` (stesso valore) — `RAG_SERVICE_TOKEN` in
  FitFrame, `API_TOKEN` in `fitframe-rag`. Laravel deve mandarla
  nell'header `Authorization: Bearer <token>` ad ogni chiamata a `/ask`;
  `fitframe-rag` la verifica prima di processare la domanda, rispondendo
  `401` se manca o non corrisponde. Manca ancora l'implementazione lato
  Laravel (Fase 2) — la chiave esiste già, va solo letta e inviata.
- Cronologia delle conversazioni salvata per utente (tabella
  `chat_messages`), lato Laravel.
- Rate limiting sulla rotta (`throttle:chat`), stesso pattern già usato per
  login/password-reset in `routes/backend.php`.
- Lingua delle risposte: italiano, coerente con `APP_LOCALE=it`.
- Accesso: tutti gli utenti autenticati del gestionale (nessuna
  restrizione per ruolo).

## Attività

### Fase 1 — Database e modelli
- [x] Migration `chat_messages` (id, user_id FK, question, answer,
      timestamps)
- [x] Modello `ChatMessage` (`belongsTo(User::class)`)

### Fase 2 — Servizio di integrazione
- [x] Service (`App\Services\Chat\RagServiceClient`) che incapsula la
      chiamata `POST /ask` verso `fitframe-rag`, legge l'URL base da
      config/env (`RAG_SERVICE_URL`, default `http://127.0.0.1:5002`)
- [x] Legge `RAG_SERVICE_TOKEN` da `config/services.php`/`.env` (valore
      già presente, condiviso con `API_TOKEN` in `fitframe-rag`) e lo
      invia come header `Authorization: Bearer <token>` ad ogni chiamata
      a `/ask` (via `Http::withToken()`)
- [x] Gestione esplicita di timeout/servizio non raggiungibile/`401`/`503`
      → `RagServiceUnavailableException`, tradotta dal controller in
      risposta di fallback controllata (503 + messaggio), non un errore
      500 generico

### Fase 3 — Controller e rotte
- [x] `App\Http\Controllers\Backend\ChatController@ask` (POST): riceve la
      domanda, chiama `RagServiceClient`, salva `ChatMessage`, ritorna
      JSON
- [x] Rotta in `routes/backend.php`, dentro il gruppo `auth`, con
      `throttle:chat`
- [x] Definito il limiter `chat` (10 richieste al minuto per utente, in
      `AppServiceProvider`)

### Fase 4 — Test
- [x] Test feature per l'endpoint chat (richiede autenticazione, mock
      della chiamata HTTP con `Http::fake()`, throttle rispettato,
      gestione del caso "servizio Python non raggiungibile") — generati
      con `/tanas:test`, 142/142 test verdi (131 preesistenti + 11 nuovi)
- [x] Test unit per `RagServiceClient` (richiesta corretta, parsing della
      risposta, gestione errori)

### Fase 5 — Frontend (widget)
- [x] ⚠️ **Checkpoint obbligatorio prima di iniziare**: mockup presentato
      (canvas con 3 stati — chiuso, aperto chiaro, aperto scuro) e
      approvato esplicitamente dall'utente prima di scrivere il codice
- [x] Blade component `backend.layouts.components.chat-widget` (floating
      button + pannello, riusa i token colore/ombra già esistenti in
      `variables.css`, non nuove classi Bootstrap `bg-popover`/`rounded-3`
      dirette ma lo stesso linguaggio visivo)
- [x] JS vanilla (`public/js/backend-chat.js`, incluso in `app.blade.php`
      accanto a `backend.js`, stesso pattern di cache-busting con
      `filemtime()`) — apre/chiude il pannello, invia la domanda via
      `fetch` con header CSRF, mostra un indicatore "sta scrivendo" e la
      risposta (o un messaggio di errore se il servizio non risponde)
- [x] Include del component in `resources/views/backend/layouts/app.blade.php`
      (solo pagine autenticate — il login usa un layout standalone
      separato, senza il widget)
- [x] Nuovo foglio di stile `public/css/backend/chat-widget.css`,
      aggiunto al loop di stylesheet già esistente in `app.blade.php`
- [x] Meta tag `csrf-token` aggiunto in `app.blade.php` (necessario per
      la chiamata `fetch` POST del widget)

### Fase 6 — Cronologia visibile nel widget (aggiunta dopo il test manuale)
- [x] Emerso testando manualmente l'integrazione end-to-end: i messaggi
      erano già persistiti in `chat_messages`, ma il widget non li
      recuperava al reload della pagina — mostrava solo la conversazione
      accumulata nella sessione del browser corrente
- [x] Relazione `User::chatMessages()` (`hasMany(ChatMessage::class)`)
- [x] View composer su `backend.layouts.components.chat-widget` in
      `AppServiceProvider` (`Auth::user()->chatMessages()->latest()->take(20)`),
      nessun endpoint HTTP aggiuntivo: la cronologia è renderizzata
      server-side nell'HTML iniziale della pagina, coerente con
      l'assenza di build JS/SPA nel resto del gestionale
- [x] Isolamento verificato con test: la cronologia di un utente non è
      visibile a un altro utente autenticato

### Fase 7 — Paginazione della cronologia (a richiesta dell'utente)
- [x] Le 20 più recenti bastano per riprendere il contesto, ma non danno
      accesso allo storico più vecchio — aggiunto un caricamento
      esplicito "on demand", non un aumento del limite fisso (che
      avrebbe appesantito ogni caricamento di pagina del gestionale)
- [x] `GET chat/history` (nome `backend.chat.history`, `before_id`
      obbligatorio): ritorna le 20 domande/risposte precedenti a quella
      indicata, in ordine cronologico, più `has_more`
- [x] Pulsante "Carica cronologia precedente" in cima al pannello,
      nascosto quando non c'è altro da caricare; ad ogni click recupera
      il prossimo blocco e lo inserisce sopra i messaggi già visibili,
      mantenendo la posizione di scroll
- [x] Isolamento verificato con test: `before_id` non permette di
      leggere messaggi di un altro utente

## Migration necessarie

| Tabella | Colonne principali | Note |
|---|---|---|
| chat_messages | id, user_id (FK users), question, answer, timestamps | unica tabella lato Laravel; nessun `gym_id` — il gestionale è unico per tutte le palestre |

## Rotte da aggiungere

| Metodo | URI | Controller/Azione | Note |
|---|---|---|---|
| POST | chat | ChatController@ask | dentro `Route::domain(admin_domain)`, nome `backend.chat`, middleware `auth` + `throttle:chat` — nessun prefisso `backend/` nell'URI (coerente con le altre rotte in `routes/backend.php`, es. `dashboard`, `setup/hero`) |
| GET | chat/history | ChatController@history | nome `backend.chat.history`, middleware `auth`, nessun throttle (sola lettura, scoperta all'utente autenticato) |

## Rischi e note

- **Dipendenza da un servizio esterno**: se `fitframe-rag` non è in
  esecuzione (processo nativo, nessun supervisor su Windows),
  la rotta `chat` deve fallire in modo controllato ("assistente
  temporaneamente non disponibile"), non con un errore generico. L'avvio
  del servizio Python in locale è verosimilmente manuale durante lo
  sviluppo — da rivalutare se il progetto arriva in produzione.
- **Verifica del modello Groq** (fatta durante la pianificazione, non da
  ripetere): confermato dal progetto `ai-chat` che `openai/gpt-oss-120b`
  funziona per la chat completion; tier gratuito sufficiente per un
  chatbot interno a basso traffico (30 RPM / 1.000 RPD / 8.000 TPM /
  200.000 TPD, nessuna carta di credito richiesta). Groq **non** copre gli
  embeddings — per questo la pipeline di embedding vive nel servizio
  Python (modello locale, stesso approccio di `ai-chat`), non in Laravel.
- **Progetto Python separato, non riuso dell'istanza `ai-chat`**: caso
  d'uso diverso (corpus fisso di documentazione vs upload di documenti
  per sessione, senza persistenza). Il codice di `ai-chat` è stato
  adattato come punto di partenza, non l'istanza in esecuzione.
  `fitframe-rag` ha il proprio piano di implementazione e la propria
  documentazione, fuori dallo scope di questo documento.
- I contenuti Markdown della base di conoscenza vanno scritti/mantenuti a
  mano dall'utente, **dentro `fitframe-rag`**; questo piano copre
  solo l'infrastruttura lato Laravel (connessione, persistenza, widget),
  non la stesura dei testi né la loro collocazione.
- **Il database `chat_messages` resta di proprietà esclusiva di
  Laravel**: nel flusso attuale il servizio Python è stateless (riceve la
  domanda, restituisce la risposta, non tocca alcun database) e non ha
  né accesso né bisogno di leggere/scrivere `chat_messages`. Non va
  condiviso il file SQLite tra i due processi — rischio concreto di
  "database is locked" (SQLite permette una sola scrittura alla volta) e
  accoppiamento nascosto tra due repository versionati/deployati
  separatamente (una migration Laravel che cambia una colonna
  romperebbe silenziosamente una query SQL diretta lato Python). Se in
  futuro `fitframe-rag` avrà davvero bisogno di leggere lo storico
  delle conversazioni, la soluzione è un endpoint Laravel dedicato
  (stesso pattern HTTP già usato per la domanda/risposta), non l'accesso
  diretto al database.

## Annotazioni integrate

> NOTA: i nuovi file .md vorreri che fosssero organizzati dentro della
> cartela docs/help, non in resources, cosi diventa piou organizato e
> intutivo trovare quando bisogno.

— **Integrata, poi superata**: inizialmente spostata a `docs/help/*.md`
in questo repository (era `resources/help/*.md`). Dopo la revisione
dell'architettura a due componenti, la collocazione finale è cambiata
ancora: i file Markdown vivono in `fitframe-rag`, non in FitFrame —
vedi "Approccio tecnico" e la nota integrata sotto sulla verifica Groq.

> NOTA: prima di implementare in frontend annalisare bene il design e
> corfermare prima di esecutare il piano.

— **Integrata**: aggiunto checkpoint esplicito all'inizio della Fase 5
("Frontend (widget)").

> NOTA: fare una verifica se l'api di groq schelta è veramente capace di
> gestire tutto, gereare embeddings e riposndere al utente, potrai fare
> una analise dentro del projeto existente locale ai-chat, che è stato
> scrito in python, verificare se funzionerebbe com php, li torvi anche
> la chiave da insereire nel .env. Verificare che il servizio groq per
> questa implementazione rimarà gratuito per quello che serve.

— **Integrata**: verifica fatta analizzando `ai-chat/api.py` e
`ai-chat/rag.py` — Groq copre solo la chat completion (confermato,
gratuito per l'uso previsto), **non** gli embeddings (che nel progetto di
riferimento usano un modello locale HuggingFace, non Groq). Questo ha
portato a rivedere l'intera architettura da "tutto in Laravel" a due
componenti separati (vedi "Approccio tecnico"), con un nuovo progetto
Python dedicato (`fitframe-rag`) invece di dipendere da un'API di
embeddings esterna non verificata (HuggingFace Inference API, scartata
per rischio di affidabilità/costo futuro ed esposizione dei dati a un
terzo servizio).
