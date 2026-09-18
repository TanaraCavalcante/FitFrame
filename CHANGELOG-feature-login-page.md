# Changelog - feature/login-page

Tutte le modifiche rilevanti al branch `feature/login-page` sono documentate in questo file.

Il formato è basato su [Keep a Changelog](https://keepachangelog.com/it/1.1.0/).

## [2026-09-18] - Redesign di "Reimposta password"

### Aggiunto
- `resources/views/backend/auth/reset-password.blade.php`: portata sullo stesso stile di login/forgot-password (layout `backend.layouts.guest`, icone nei campi email/password, toggle mostra/nascondi su entrambi i campi password, link "Torna al login") — prima era l'ultima pagina di autenticazione rimasta nel vecchio markup Bootstrap grezzo.

## [2026-09-17] - Fix diagnostica editor e allineamento logo sidebar

### Corretto
- `app.blade.php` e `guest.blade.php`: il loop degli stylesheet backend costruiva il path direttamente dentro `asset()`/`public_path()` con interpolazione (`"css/backend/{$var}.css"`), facendo scattare falsi positivi "Asset not found" nell'estensione Laravel dell'editor (impossibilitata a risolvere staticamente il valore). Ora il path è calcolato prima in una variabile via `@php`.
- `aside.blade.php`: stesso problema per `route($child['route'])` nel loop del menu Setup (l'estensione arrivava persino a segnalare "Route [route] not found", confondendo la chiave dell'array con il nome della rotta) — estratto in `$childRoute`.
- `public/css/backend/generals.css`: la logo della sidebar (sostituita di recente con una versione più larga) veniva tagliata — non era il `max-width` del contenitore il vincolo reale, ma la larghezza fissa della sidebar stessa (260px). Altezza del logo ridotta da 75px a 45px (la larghezza si adatta da sola via `width: auto`, proporzioni invariate).
- Riga della logo nella sidebar: aggiunto `ps-3` per allinearla orizzontalmente con le icone delle voci di menu sottostanti (che partono a 16px, mentre la logo partiva da 0).

### Aggiunto
- `.vscode/settings.json` (non versionato, escluso da `.gitignore`): `tailwindCSS.validate: false` — il progetto usa Bootstrap, non Tailwind; l'estensione Tailwind IntelliSense segnalava le classi Bootstrap (`flex-grow-1`, `flex-shrink-0`) come da riscrivere nella forma "canonica" Tailwind.

## [2026-09-17] - Layout guest condiviso e redesign di "Password dimenticata"

### Aggiunto
- `resources/views/backend/layouts/guest.blade.php`: layout condiviso (`@extends`/`@yield`, stesso pattern di `backend.layouts.app`) per le pagine di autenticazione non autenticate — head, shell split marca/form una sola volta.
- `resources/views/backend/auth/forgot-password.blade.php`: riportata allo stesso stile del login (icona email, card, bottone), ora costruita sul layout `guest` — prima era ancora nel vecchio markup Bootstrap grezzo.
- Testo del pannello di marca personalizzabile per pagina via `@yield('brand-title', ...)`/`@yield('brand-text', ...)` con default riusato dal login; "Password dimenticata" usa un testo dedicato ("Recupera l'accesso.").

### Modificato
- `login.blade.php` estratto nello stesso layout `guest` (solo `title` e `form` cambiano da pagina a pagina).

## [2026-09-17] - Redesign della pagina di login

### Aggiunto
- Layout split della pagina di login (`resources/views/backend/auth/login.blade.php`): pannello di marca + form, con grid Bootstrap (`col-lg-5`/`col-lg-7`), impilati in mobile/tablet con il form sempre in cima (`order-*`).
- `public/css/backend/auth.css`: stili dedicati al layout di autenticazione (gradiente del pannello di marca, animazione di entrata del card, ecc.), riusando i token di `variables.css`; solo ciò che non ha un equivalente diretto nelle utility di Bootstrap.
- Icone nei campi email/password e toggle mostra/nascondi password (riusa il pattern `.password-toggle` già esistente in `backend.js`).

### Modificato
- `public/js/backend.js`: aggiunte guardie (`if (elemento)`) prima di agganciare gli event listener di sidebar/tema, così lo script può essere incluso in sicurezza anche in pagine standalone come il login, che non hanno l'intero shell autenticato.
- `public/css/backend/generals.css`: `max-width` dedicato per `.backend-brand-text` (230px), la nuova logo (più larga delle label testuali) veniva tagliata dall'`overflow: hidden` esistente.
- `resources/views/backend/layouts/components/aside.blade.php`: piccoli aggiustamenti di padding attorno al logo/toggle sidebar.
- Sostituiti `public/backend/img/logo.png` e `logo-dark.png` con le nuove versioni del logo.

### Corretto
- Nessuna regressione nei test di autenticazione (`AuthenticationTest`) né nel resto della suite (151 test verdi).
