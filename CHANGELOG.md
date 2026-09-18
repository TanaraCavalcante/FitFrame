# Changelog

Tutte le modifiche rilevanti a questo progetto sono documentate in questo file.

Il formato è basato su [Keep a Changelog](https://keepachangelog.com/it/1.1.0/).

## [1.2.0] - 2026-09-18

Redesign completo delle tre pagine di autenticazione (login, password dimenticata, reimposta password), prima nel Bootstrap grezzo di default, ora coerenti con il design system del backend.

### Aggiunto
- `resources/views/backend/layouts/guest.blade.php`: layout condiviso (`@extends`/`@yield`, stesso pattern di `backend.layouts.app`) per le pagine di autenticazione non autenticate — head, shell split marca/form definiti una sola volta. Login, "Password dimenticata" e "Reimposta password" ora vi si appoggiano, cambiando solo `title`, `form` e — dove serve — il testo del pannello di marca (`@yield('brand-title', ...)`/`@yield('brand-text', ...)`, es. "Recupera l'accesso." per il reset).
- Layout split (pannello di marca + form) con grid Bootstrap (`col-lg-5`/`col-lg-7`), impilato in mobile/tablet con il form sempre in cima.
- Icone nei campi email/password, toggle mostra/nascondi password su tutti i campi password, animazione di entrata del card.
- `public/css/backend/auth.css`: stili dedicati al layout di autenticazione (gradiente del pannello di marca, ecc.), riusando i token di `variables.css` — solo ciò che non ha un equivalente diretto nelle utility di Bootstrap.

### Modificato
- `public/js/backend.js`: aggiunte guardie (`if (elemento)`) prima di agganciare gli event listener di sidebar/tema, così lo script può essere incluso in sicurezza anche in pagine standalone senza l'intero shell autenticato.
- Sostituiti `public/backend/img/logo.png` e `logo-dark.png` con le nuove versioni del logo.

### Corretto
- Logo della sidebar (tagliata dalla nuova versione più larga): non era il `max-width` del contenitore il vincolo reale ma la larghezza fissa della sidebar (260px) — altezza del logo ridotta da 75px a 45px (proporzioni invariate via `width: auto`) e allineata alle icone del menu (`ps-3`).
- Falsi positivi "Asset/Route not found" nell'estensione Laravel dell'editor, causati da `asset()`/`route()` con path costruiti per interpolazione dentro loop — path ora calcolati prima in una variabile via `@php`.

Lo storico dettagliato, commit per commit, di questa feature è stato consolidato da `CHANGELOG-feature-login-page.md` (rimosso dopo il merge in questa entry).

## [1.1.0] - 2026-09-17

Chatbot di aiuto contestuale nel gestionale, basato su RAG — architettura a due componenti: FitFrame gestisce autenticazione, rate limiting, persistenza della cronologia e widget; un servizio Python separato (`fitframe-rag`, non in questo repository) possiede l'intera pipeline RAG (base di conoscenza, embeddings, ricerca, chiamata a Groq).

### Aggiunto
- `POST chat` (`ChatController@ask`): valida la domanda, la inoltra a `RagServiceClient` (chiamata HTTP a `fitframe-rag` con `Authorization: Bearer`), salva la coppia domanda/risposta in `ChatMessage`, ritorna la risposta in JSON o un fallback controllato (503, `RagServiceUnavailableException`) se il servizio non è raggiungibile.
- `GET chat/history` (`ChatController@history`): pagina la cronologia dell'utente autenticato 20 messaggi alla volta, a partire da un `before_id`, con `has_more` per sapere se restano altre pagine.
- Migration e modello `ChatMessage` (`user_id`, `question`, `answer`), relazione `User::chatMessages()`.
- Widget di chat flottante (Blade component `backend.layouts.components.chat-widget`, incluso solo nelle pagine autenticate del backend): apertura/chiusura, invio della domanda via `fetch`, indicatore "sta scrivendo", cronologia visibile persistita tra reload tramite view composer (ultime 20 coppie renderizzate server-side) con paginazione "Carica cronologia precedente".
- Rate limiter dedicato `chat` (10 richieste al minuto per utente), per contenere i costi verso il servizio RAG/Groq.
- Config `services.rag` (`RAG_SERVICE_URL`/`RAG_SERVICE_TOKEN`).
- `docs/plan-chatbot-rag-gestionale.md`: piano di implementazione (architettura, fasi, checkpoint di design).

### Corretto
- Migration `chat_messages` non applicata al database MySQL locale di sviluppo (solo alla SQLite in-memory dei test) — il widget falliva con "Assistente temporaneamente non disponibile" al primo test manuale end-to-end.

Lo storico dettagliato, commit per commit, di questa feature è stato consolidato da `CHANGELOG-feature-rag.md` (rimosso dopo il merge in questa entry).

## [1.0.6] - 2026-07-29

### Aggiunto
- `README.md` completo: stack, funzionalità, installazione, domini locali multi-tenant, utenti di test, migration/seeder, gestione temi, test.

## [1.0.5] - 2026-07-29

### Aggiunto
- Sezione Piani (`#plans`): sfondo con gradiente diagonale animato (`--color-background` → `--color-surface`), rispetta `prefers-reduced-motion`.
- Bordo (`--color-background`) sulle card piano non in evidenza, per delimitarle dal nuovo sfondo animato.

## [1.0.4] - 2026-07-29

### Aggiunto
- Foto del team (Elena Ferraro, Marco Villa, Sara Bianchi) per il tema Zenflow.

## [1.0.3] - 2026-07-29

### Corretto
- `.team-card__overlay`: gradiente sulla card del team troppo chiaro alla base, nome poco leggibile — stop intermedio più solido, gradiente comunque mantenuto.

## [1.0.2] - 2026-07-29

### Aggiunto
- Favicon per-tema in tutte le view (frontend e backend), con fallback automatico al tema `base` se il tema attivo non ha un proprio `favicon.ico`.

### Modificato
- Frontend: `<title>` usa il nome della struttura (`$gym->name`) invece del nome dell'app.
- Backend: `<title>` prefissato con `FitFrame | `, mantenendo il titolo specifico di ogni pagina.

## [1.0.1] - 2026-07-29

### Aggiunto
- `GymSeeder`: contenuto completo per la struttura Zenflow (Yoga/Pilates) — testi Hero/CTA/footer, 4 pratiche (Hatha Yoga, Pilates, Meditazione, Yin Yoga), 3 piani (Essenziale/Equilibrio/Pienezza), 3 istruttori, 3 testimonianze — applicato anche ai dati già esistenti in ambiente locale.

## [1.0.0] - 2026-07-29

Prima release: pannello admin (gestionale) completo per la gestione multi-tenant delle palestre e del contenuto di ogni landing page pubblica.

### Aggiunto

**Autenticazione e autorizzazione**
- Pannello admin su dominio dedicato (`ADMIN_DOMAIN`, configurabile via `.env`), routing separato dal sito pubblico.
- Login/logout manuale con rate limiting, reset password via email (notifiche core Laravel).
- Ruoli `super_admin`/`gym_admin` (colonna `role` + `gym_id` su `User`), Policy dedicate per `Gym` e `User`.
- Impersonation (il super_admin assume l'identità di un gym_admin).

**CRUD di struttura** (solo super_admin)
- Strutture (Gym): crea automaticamente le 6 sezioni riordinabili della home pubblica alla creazione.
- Utenti (gym_admin) e Super Admin, con separazione nome/cognome, toggle mostra/nascondi password, filtri di ricerca lato server.

**Layout backend**
- Sidebar collassabile (hover-preview), tema chiaro/scuro persistito, breadcrumb, avatar/popover utente, tipografia dedicata (Inter).
- Conferma di eliminazione con SweetAlert2 al posto del `confirm()` nativo.

**CRUD di contenuto per sezione** (super_admin o gym_admin scoperto alla propria struttura, selettore struttura per il super_admin in ogni pagina)
- Hero: testo + fino a 3 immagini o 1 video (mutuamente esclusivi), upload via Spatie MediaLibrary.
- Corsi, Piani, Team, Testimonianze: liste riordinabili (su/giù), upload foto nel Team, caratteristiche dinamiche nei Piani, un solo piano "in evidenza" per struttura.
- Galleria: 5 slot immagine indipendenti, indicatore visivo (check/x) di quale slot ricade sul fallback del tema.
- CTA finale + Contatti: titolo/sottotitolo/pulsante della CTA, slogan del footer, dati di contatto (indirizzo, telefono, email, WhatsApp, Instagram, orari).
- Ordina sezioni: riordina le 6 sezioni della home + modifica il titolo di ognuna (tranne la CTA, che ha già un campo proprio).
- Tab in stile underline di Bootstrap (`.nav-underline`) per navigare tra i blocchi di una stessa pagina (Hero, CTA, Ordina sezioni), invece di card separate.

**Frontend pubblico**
- Carousel manuale (frecce) in Corsi/Piani quando il numero di elementi supera quello che entra per riga.
- Carousel automatico in loop (CSS puro, nessuna libreria) in Team/Testimonianze, con soglia responsiva per breakpoint, pausa al passaggio del mouse, disattivato per `prefers-reduced-motion`.
- Fallback all'immagine di default del tema quando non c'è un upload (Galleria, Team).

**Altro**
- Traduzione italiana dei messaggi di validazione di Laravel (`lang/it/validation.php`), mancante da quando il locale è `it`.
- Trait condivisi: `HasOrderedSiblings` (riordino per FK del genitore) e `ResolvesGymFromRequest` (risoluzione della struttura da `?gym_id=` o dalla struttura dell'utente).

### Modificato
- CSS del backend riorganizzato in `variables`/`tipografia`/`generals`/`generics`, ognuno linkato singolarmente con cache-busting proprio.
- Validazione slug delle Strutture: campo "Tema" da select limitata a input libero (`alpha_dash`), con fallback al tema `base` se non esiste ancora.

### Corretto
- N+1 diffusi: `orderBy('order')` mancante su varie relazioni (`gymClasses`, `plans`, `personalTrainers`, `testimonials`, `gymSections`), eager-load di `gym.media` mancante in `ResolveGym`.
- Bug di seed: creare una struttura via admin non generava le righe `gym_sections` di default, lasciando la sua home pubblica priva di tutte le sezioni riordinabili.
- `ParseError` intermittente causato da commenti Blade (`{{-- --}}`) lasciati dentro blocchi `@php...@endphp` (lì dentro Blade non compila nulla, è PHP puro).
- Vari fix di layout responsive (griglie che lasciavano un buco laterale con meno elementi del previsto, marquee Team non a capo correttamente in tablet, badge tagliato dall'`overflow: hidden` nel carousel Piani).

## [Riferimento]

Lo storico dettagliato, commit per commit, di questa release è stato consolidato da `CHANGELOG-feature-backend.md` (rimosso dopo il merge in questa entry).
