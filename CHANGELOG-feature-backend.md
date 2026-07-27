## [2026-07-27] - Gestione contenuti Hero (testo, immagini, video)

### Aggiunto
- Pagina `backend/setup/hero.blade.php` (rotte `backend.setup.hero`/`.update`): tab "Testo"/"Visual" nel pannello admin, selettore struttura per il super_admin.
- `Gym` implementa `HasMedia`/`InteractsWithMedia` (spatie/laravel-medialibrary, installato ma inutilizzato finora): collection `hero_image_1/2/3` (singleFile ciascuna) e `hero_video` (singleFile).
- Sezione Visual: 3 slot immagine indipendenti con anteprima, upload e pulsante di rimozione per slot; slot video con anteprima, upload e rimozione; radio "Immagini"/"Video" per scegliere quale sia attivo — `hero_visual_mode` salvato in `contents`, senza cancellare l'altro set di file (si può tornare indietro senza ricaricare nulla).
- Validazione client-side del video (formato/dimensione) in `backend.js`, in aggiunta a quella server-side.
- `config/media-library.php` pubblicato, `max_file_size` allineato a 50MB (era 10MB di default: causava un 500 non gestito su upload validi lato Laravel ma rifiutati dal pacchetto).
- Frontend pubblico (`elements/hero.blade.php`): legge `hero_visual_mode` per mostrare immagine (casuale se più di una) o video di sfondo, con fallback al file statico del tema se non configurato.
- Alert di sessione (`success`/`error`) resi `alert-dismissible` con pulsante di chiusura.
- 12 test per `HeroController` (autorizzazione, cambio struttura, testo, upload/rimozione immagini e video, persistenza del `visual_mode`).

## [2026-07-26] - Componente filtro nelle index e utility btn-light/btn-purple

### Aggiunto
- Componente `<x-filter-toggle>` (`resources/views/components/filter-toggle.blade.php`): pulsante "Filtri" collassabile (Bootstrap collapse) con badge del numero di filtri attivi, sopra la tabella.
- Filtri lato server (query string, applicati con `->when()` nel controller): Strutture (ricerca su nome/tema/dominio), Utenti (ricerca + struttura), Super Admin (ricerca).
- Stato vuoto (`@forelse`/`@empty`) nelle tre index quando il filtro non trova risultati, con icona e messaggio "Nessun risultato trovato.".
- Utility `.btn-light-{primary,secondary,success,danger,warning,info}` in `generics.css` (stile Metronic, basate sulle CSS variable native di Bootstrap `--bs-{colore}-rgb`).
- Colore viola "info" di Metronic (`--backend-purple` / `--backend-purple-rgb`, valori esatti presi dal CSS compilato di maelstrom) con `.btn-purple`, `.btn-outline-purple`, `.btn-light-purple`.
- Test dei filtri per i tre controller (ricerca, filtro per struttura).

## [2026-07-26] - Toggle mostra/nascondi password nei form Utenti/Super Admin

### Aggiunto
- Pulsante occhio (mostra/nascondi) sul campo Password in Utenti e Super Admin, via `input-group` + `.password-toggle` in `backend.js` (riutilizzabile per altri campi password futuri).
- `autocomplete="off"`/`"new-password"` su email/password per impedire l'autofill del browser con credenziali salvate.

### Modificato
- Campo Password in creazione non ha più il valore `12345678` precompilato — resta vuoto, il super_admin sceglie la password.

## [2026-07-26] - Separazione nome/cognome per gli utenti

### Aggiunto
- Migrazione `add_surname_to_users_table` (idempotente, default `''`), colonna mirrorata in `User::$attributes`.
- Campo "Cognome" nei form di Utenti e Super Admin, accanto a "Nome" (`col-md-6` ciascuno).

### Modificato
- `User::initials()` ora usa direttamente `name`+`surname` invece di fare parsing di una stringa unica.
- Tutti i punti che mostravano solo `name` (dashboard, avatar/popover dell'aside, tabelle index di Utenti e Super Admin) ora mostrano nome e cognome insieme.
- `UserFactory` genera `name`/`surname` separati (`firstName()`/`lastName()`); `AdminsSeeder` aggiornato di conseguenza.
- Le 4 Form Request di Utenti/Super Admin validano `surname` come `required`.

## [2026-07-26] - Form Utenti/Super Admin: stesso layout a card di Strutture

### Modificato
- Create/edit di Utenti e Super Admin nel layout a due card (header con CTA "Indietro"/"Salva" — sempre "Salva", anche in creazione), come Strutture.
- Form in grid Bootstrap con `.required` sui campi obbligatori; il campo Password è `required` solo in creazione (in modifica resta facoltativo, "lascia vuoto per non cambiarla").

## [2026-07-26] - Form Strutture: layout a card, grid e slug libero

### Aggiunto
- Create/edit di Strutture nel layout a due card (header con CTA "Indietro"/"Crea"/"Salva", body col form), come le index.
- Form Strutture in grid Bootstrap: Nome `col-12`, Tema e Dominio `col-md-6`.
- Classe `.required` in `generics.css` (asterisco rosso dopo la label, stile Metronic) applicata ai campi obbligatori del form Strutture.

### Modificato
- Campo "Tema" del form Strutture: da `<select>` (limitato ai temi già installati) a input di testo libero, default `base` — permette di assegnare uno slug per un tema non ancora costruito.
- Validazione slug (`Store`/`UpdateGymRequest`): non più `Rule::in(temi installati)`, ora solo `alpha_dash`.
- `ResolveGym`: se il tema dello slug non esiste (`Theme::exists()`), il sito resta sul tema `base` invece di errore/tema mancante.
- `GymController` non passa più `$themes` alle view create/edit (non più necessario).

## [2026-07-26] - Layout a card nelle index di Strutture/Utenti/Super Admin

### Modificato
- Header e tabella delle index di Strutture, Utenti e Super Admin ora dentro due `.card` separate (`border-0`, senza bordo) con spazio tra loro, per un aspetto più pulito.
- Rimosso `table-striped` da tutte le tabelle (rimaneva solo su Super Admin), uniformando a `table-hover`.

## [2026-07-26] - Azioni icon-only nelle tabelle di Strutture/Utenti/Super Admin

### Aggiunto
- Pulsanti azione (Modifica, Elimina, Impersona) uniformati a icon-only (`btn-sm` outline + FontAwesome) con `title` per accessibilità, nelle index di Strutture, Utenti e Super Admin.
- Pulsante "Reset password" (solo super_admin) nell'index di Utenti — solo UI, nessuna rotta/logica ancora.
- Pulsanti "Indietro" / "Nuovo" uniformati nell'header delle tre index.

## [2026-07-26] - Breadcrumb per Strutture/Utenti/Super Admin e fix scroll aside

### Aggiunto
- Breadcrumb dedicato (home → sezione → pagina corrente) su index/create/edit di Strutture, Utenti e Super Admin.
- `.fs-7` in `tipografia.css` (.85rem, via media tra `fs-6` di Bootstrap e `.fs-8`), usata nei breadcrumb.
- Header di sezione dell'aside (`Dashboard`, `Amministrazione`, `Gestione`) ora `position: sticky` dentro `.backend-aside-nav` — restano visibili durante lo scroll del menu, con sfondo opaco per coprire le voci che scorrono sotto.

### Corretto
- `.mx-n3` non esiste in questo bundle Bootstrap (margini negativi disabilitati di default) — il nav dell'aside non copriva più la larghezza piena. Sostituito rimuovendo il padding orizzontale dall'`<aside>` e spostandolo su header/user-menu.
- `.nav` di Bootstrap imposta `flex-wrap: wrap`: con l'altezza dell'aside ora fissa, generava una colonna fantasma quando l'accordion Setup si espandeva. Aggiunto `flex-nowrap`.
- Cache-busting rotto: `backend.css` versionava solo l'aggregatore, non i quattro parziali `@import`ati, quindi le modifiche CSS potevano restare in cache del browser. Rimosso l'aggregatore, ogni parziale è ora linkato singolarmente con `filemtime()` proprio.

## [2026-07-26] - Menu aside con sezioni per ruolo e accordion Setup

### Aggiunto
- Aside organizzato in sezioni: header "Dashboard", header "Amministrazione" (solo super_admin: Strutture, Super Admin, Utenti in ordine alfabetico), header "Gestione" (entrambi i ruoli) con accordion "Setup" (Bootstrap collapse).
- Voci figlie di Setup (Hero, Team, Galleria, Testimonianze, CTA, Ordina sezioni) protette da `Route::has()` — disabilitate con tooltip finché le rotte non esistono.
- `.text-gray-muted` in `generics.css`, agganciata a `var(--bs-secondary-color)` (stessa variabile del sottotitolo dashboard) per i header di sezione.
- `.fs-8` in `tipografia.css` per le label dei header, più piccole della scala `fs-*` di Bootstrap (che si ferma a `fs-6`).

### Modificato
- `.hover-accent` torna al colore pieno del testo a riposo (`--backend-aside-color`) invece del muted — il muted resta riservato ai soli header di sezione.
- `DashboardTest` aggiornato per verificare le voci di menu viste da super_admin e gym_admin.

## [2026-07-25] - Navbar allineata all'aside

### Modificato
- Navbar del pannello admin senza bordo, sfondo/colore uguali all'aside in entrambi i temi (stessa variabile `--backend-aside-bg`).

## [2026-07-25] - Rifinitura avatar/popover utente e pulizia CSS backend

### Aggiunto
- `User::initials()` (iniziali per l'avatar, es. "W Tech Admin" → "WA") con test unitari dedicati.
- `.btn-back-primary`, `.bg-popover`, `.bg-aside-subtle` in `generics.css` — utility riutilizzabili basate sulle variabili tema, applicate a bottone logout e popover utente.
- `--backend-popover-bg` e `--backend-accent-shadow` in `variables.css` (chiaro/scuro) per lo sfondo del popover e l'ombra in accent.

### Modificato
- Popover utente nell'aside (avatar, nome, email/nome struttura) convertito da CSS puro a utility Bootstrap + variabili tema; menu spostato in fondo all'aside.
- Nell'aside, il gym_admin vede il nome della struttura al posto dell'email (l'email resta visibile solo nel popover).
- Rimossi stili CSS puri ormai ridondanti (`.backend-outline-button`, `.backend-icon-button`, bordi/colori statici assorbiti dalle utility) e il bordo destro dell'aside (rimosso in entrambi i temi).
- `AdminsSeeder`: nome del super_admin aggiornato a "Tanara Cavalcante".

## [2026-07-25] - Logo, breadcrumb e stato attivo del menu

### Aggiunto
- Logo del pannello admin (chiaro/scuro, swap automatico via tema) in `public/backend/img/`.
- Breadcrumb nella navbar (`@yield('breadcrumb')` con fallback icona home) al posto del titolo statico.
- `public/css/backend/generics.css` con utility `.hover-accent` (colore accent del tema al hover/focus), sezioni commentate in `generals.css` (Main/Sidebar).

### Modificato
- Voce di menu della pagina corrente resta colorata con l'accent (senza sfondo) invece di sparire — sfondo compare solo su hover/focus, anche sulla voce attiva.

## [2026-07-25] - Riorganizzazione CSS backend e tipografia

### Aggiunto
- Tipografia dedicata al pannello admin: font Inter (Google Fonts) con `tabular-nums` per allineare i numeri in tabelle/report, in `public/css/backend/tipografia.css`.

### Modificato
- `public/css/backend.css` diviso in `backend/variables.css` (design token), `backend/tipografia.css`, `backend/generals.css` (stili componenti) — `backend.css` resta l'unico file caricato dal layout, ora solo un aggregatore `@import`.
- Rimosso `public/css/app.css` (vuoto, inutilizzato) e il relativo `<link>` in `master.blade.php`.

## [2026-07-25] - Pannello admin backend completo

### Aggiunto
- Pannello admin completo su dominio dedicato (`ADMIN_DOMAIN`, configurabile via `.env`): login/logout, reset password via email, CRUD Strutture (Gym), Utenti (gym_admin), Super Admin, impersonation.
- Layout backend con sidebar collassabile (hover-preview), tema chiaro/scuro persistito, componenti `aside`/`navbar` separati.
- `AdminsSeeder` per popolare un super_admin e un gym_admin di prova.

### Modificato
- Rotte del pannello admin spostate da prefisso `/admin` a routing per dominio (`Route::domain(config('app.admin_domain'))`), letto da `.env` invece di essere hardcoded.
- Alert di sessione (`success`/`error`) centralizzati nel layout condiviso invece che ripetuti per singola vista.

### Corretto
- Suite di test aggiornata per il nuovo routing per dominio (rimossi i vecchi path `/admin/...`).

## [2026-07-25] - Design e piano del pannello admin backend

### Aggiunto
- Spec di design del pannello admin (`docs/superpowers/specs/2026-07-25-backend-admin-panel-design.md`): ruoli super_admin/gym_admin, autenticazione, routing separato dal sito pubblico, CRUD Strutture/Utenti, impersonation.
- Piano di implementazione (`docs/superpowers/plans/2026-07-25-backend-admin-panel.md`): 10 task da eseguire con TDD, dalla migrazione `role`/`gym_id` fino ai CRUD completi.
