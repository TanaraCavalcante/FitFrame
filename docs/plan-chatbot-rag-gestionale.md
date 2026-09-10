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

Architettura a **due componenti, in due repository separati**:

1. **`fitframe-help-rag`** — nuovo progetto Python, sibling di FitFrame
   (`C:\laragon\www\fitframe-help-rag`), non ancora creato. Pipeline RAG
   completa: legge i `.md` di aiuto, chunking, embeddings locali
   (HuggingFace `sentence-transformers`, stesso modello già validato nel
   progetto `ai-chat`), indicizzazione FAISS, ricerca per similarità,
   costruzione del prompt, chiamata alla chat completion di **Groq**
   (modello `openai/gpt-oss-120b`, già usato con successo in `ai-chat`),
   ritorna la risposta. Gira come **processo nativo (venv)**, non
   containerizzato. Codice adattato dal pattern già testato in
   `ai-chat/rag.py` e `ai-chat/api.py` — **non riutilizza l'istanza in
   esecuzione di `ai-chat`**: caso d'uso diverso (corpus fisso di
   documentazione vs upload di documenti per sessione). Avrà un proprio
   piano di implementazione e una propria documentazione, nel proprio
   repository.
2. **FitFrame** (questo repository) — Laravel resta responsabile di:
   autenticazione (middleware `auth` già esistente), rotta
   `POST backend/chat` con `throttle:chat`, un client HTTP che chiama
   l'endpoint del servizio Python, persistenza di
   `{user_id, question, answer}` in `chat_messages`, e il widget di chat
   (Blade + JS vanilla) nel layout del gestionale.

**Questo piano copre solo il lato Laravel** (connessione HTTP al servizio
esterno + persistenza + frontend). Nessuna logica di chunking, embedding,
FAISS o chiamata diretta a Groq vive in questo repository.

- Fonte dei contenuti di aiuto (file Markdown, chunking, indicizzazione):
  vive interamente in `fitframe-help-rag`, **non** in questo repository —
  Laravel non conosce percorsi di file né dettagli di indicizzazione,
  riceve solo `{ domanda }` e restituisce `{ risposta }`. Contratto
  minimo tra i due sistemi, nessuna dipendenza da un percorso di
  filesystem condiviso tra i due processi.
- Cronologia delle conversazioni salvata per utente (tabella
  `chat_messages`), lato Laravel.
- Rate limiting sulla rotta (`throttle:chat`), stesso pattern già usato per
  login/password-reset in `routes/backend.php`.
- Lingua delle risposte: italiano, coerente con `APP_LOCALE=it`.
- Accesso: tutti gli utenti autenticati del gestionale (nessuna
  restrizione per ruolo).

## Attività

### Fase 1 — Database e modelli
- [ ] Migration `chat_messages` (id, user_id FK, question, answer,
      timestamps)
- [ ] Modello `ChatMessage` (`belongsTo(User::class)`)

### Fase 2 — Servizio di integrazione
- [ ] Service (es. `App\Services\Chat\RagServiceClient`) che incapsula la
      chiamata HTTP verso `fitframe-help-rag`, legge l'URL base da
      config/env (es. `RAG_SERVICE_URL`, default `http://127.0.0.1:5002`)
- [ ] Gestione esplicita di timeout/servizio non raggiungibile → risposta
      di fallback controllata all'utente, non un errore 500 generico

### Fase 3 — Controller e rotte
- [ ] `App\Http\Controllers\Backend\ChatController@ask` (POST): riceve la
      domanda, chiama `RagServiceClient`, salva `ChatMessage`, ritorna
      JSON
- [ ] Rotta in `routes/backend.php`, dentro il gruppo `auth`, con
      `throttle:chat`
- [ ] Definire il limiter `chat`

### Fase 4 — Test
- [ ] Test feature per l'endpoint chat (richiede autenticazione, mock
      della chiamata HTTP con `Http::fake()`, throttle rispettato,
      gestione del caso "servizio Python non raggiungibile") — usare
      `/tanas:test` per generare gli stub
- [ ] Test unit per `RagServiceClient` (richiesta corretta, parsing della
      risposta, gestione errori)

### Fase 5 — Frontend (widget)
- [ ] ⚠️ **Checkpoint obbligatorio prima di iniziare**: presentare
      un'analisi/mockup del design del widget (posizione, stile,
      comportamento) e attendere conferma esplicita dell'utente — non
      procedere all'implementazione di questa fase senza quel via libera
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
| chat_messages | id, user_id (FK users), question, answer, timestamps | unica tabella lato Laravel; nessun `gym_id` — il gestionale è unico per tutte le palestre |

## Rotte da aggiungere

| Metodo | URI | Controller/Azione | Note |
|---|---|---|---|
| POST | backend/chat | ChatController@ask | dentro `Route::domain(admin_domain)`, middleware `auth` + `throttle:chat` |

## Rischi e note

- **Dipendenza da un servizio esterno**: se `fitframe-help-rag` non è in
  esecuzione (processo nativo, nessun supervisor su Windows),
  `backend/chat` deve fallire in modo controllato ("assistente
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
- **Nuovo progetto Python, non riuso dell'istanza `ai-chat`**: caso d'uso
  diverso (corpus fisso di documentazione vs upload di documenti per
  sessione, senza persistenza). Il codice di `ai-chat` viene adattato come
  punto di partenza, non l'istanza in esecuzione. `fitframe-help-rag`
  avrà il proprio piano di implementazione e la propria documentazione,
  fuori dallo scope di questo documento.
- I contenuti Markdown della base di conoscenza vanno scritti/mantenuti a
  mano dall'utente, **dentro `fitframe-help-rag`**; questo piano copre
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
  futuro `fitframe-help-rag` avrà davvero bisogno di leggere lo storico
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
ancora: i file Markdown vivono in `fitframe-help-rag`, non in FitFrame —
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
Python dedicato (`fitframe-help-rag`) invece di dipendere da un'API di
embeddings esterna non verificata (HuggingFace Inference API, scartata
per rischio di affidabilità/costo futuro ed esposizione dei dati a un
terzo servizio).
