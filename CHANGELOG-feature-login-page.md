# Changelog - feature/login-page

Tutte le modifiche rilevanti al branch `feature/login-page` sono documentate in questo file.

Il formato è basato su [Keep a Changelog](https://keepachangelog.com/it/1.1.0/).

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
