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
