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
