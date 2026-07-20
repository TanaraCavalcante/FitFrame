# 1. Stack

## Backend

- **PHP 8.3**
- **Laravel 12** — framework principale, con la nuova struttura file
  introdotta dalla v11 (middleware/provider registrati in
  `bootstrap/app.php`, niente `app/Http/Kernel.php`)
- **MySQL** — database relazionale. In locale gestito con **Navicat**,
  in produzione/altrove con **phpMyAdmin**
- **Laravel Boost** — pacchetto di sviluppo che fornisce linee guida e
  strumenti MCP specifici per questa applicazione (usato solo in fase
  di sviluppo con l'AI, non influisce sull'app in produzione)
- **`igaster/laravel-theme`** (MIT, gratuito) — gestisce la risoluzione
  del tema attivo: `config/themes.php` + un `theme.json` per tema
  (`extends`, `asset-path`), helper `theme_url()` per risolvere gli
  asset del tema corrente, `Theme::set()`/`Theme::exists()`. Analizzato
  dal progetto UCUE (dev/trabalho), dove è già in uso in produzione con
  lo stesso schema "tema principale + temi figli che ereditano tutto
  tranne asset/colori". Anche se oggi le view sono identiche per tutti
  i temi (nessuna necessità di override), il pacchetto lo permetterebbe
  gratuitamente in futuro, senza dover costruire un meccanismo custom

## Frontend

- **Blade** puro — nessuna SPA, nessun framework JS
- **Nessun Vite / build step** — rimosso dal progetto iniziale
  (`create-project` includeva Vite + Tailwind di default). CSS e JS
  vengono serviti staticamente da `public/css/` e `public/js/`,
  linkati direttamente nelle view con `<link>`/`<script>`
- **Bootstrap 5** (via CDN, senza build step) — framework CSS per la
  struttura delle landing page. Bootstrap 5 usa variabili CSS native
  (`--bs-primary`, ecc.), quindi ogni tema può sovrascriverle nel
  proprio file CSS senza bisogno di ricompilare Sass. Pattern
  confermato in produzione nel progetto UCUE
  (`[data-bs-theme=light] { --bs-primary: ... }` per tema)
- **Font Awesome Free** (via CDN) — icone per modalità, social,
  contatti. Versione gratuita (non Pro), sufficiente per icone
  generiche
- **Google Fonts** (via CDN) — ogni tema può avere una propria
  famiglia tipografica e propri pesi (regular, medium, bold, ecc.),
  dichiarati insieme a colore/logo nel CSS del tema (vedi
  [3. Multi-tenant](3-multi-tenant.md))
- **Tabler** (tabler.io, MIT/gratuito) — kit UI basato su Bootstrap,
  pensato per il futuro **pannello admin** (vedi
  [5. Backend e amministrazione](5-backend-admin.md)), come
  alternativa gratuita a Metronic (a pagamento). Non usato nelle
  landing page pubbliche, solo nell'area admin quando verrà costruita
- Un file CSS per tema (`public/css/{slug}.css`) sopra Bootstrap, per
  le personalizzazioni specifiche di ogni palestra

## Ambiente locale

- **Laravel Valet** (macOS, o `valet-linux` su Linux) per servire il
  progetto su domini `.test` locali senza configurare un vhost
  manualmente
- Multi-dominio locale: `valet link {slug}` ripetuto per ogni palestra
  nella stessa cartella del progetto (vedi [3. Multi-tenant](3-multi-tenant.md))
- **Windows**: Valet non è disponibile nativamente. Alternative:
  - **Laragon** (consigliato) — bundle Apache/Nginx + PHP + MySQL con
    domini `.test` automatici per cartella, evita la configurazione
    manuale di vhost/`hosts` richiesta da XAMPP
  - **Laravel Herd** — ha una versione Windows, GUI simile a Valet
  - **WSL2 + Valet Linux** — esegue Valet vero e proprio dentro WSL2
  - XAMPP resta un'opzione, ma richiede vhost e modifica manuale del
    file `hosts` per ogni dominio locale

## Versionamento

- Repository Git privato: `github.com/TanaraCavalcante/FitFrame`
  (account personale)
- `.env` mai committato (già in `.gitignore`)

## Cosa NON fa parte dello stack

- Nessun bundler JS (Vite/Webpack) — JS vanilla se necessario
- Nessun ORM/DB diverso da Eloquent + MySQL
- Tailwind — rimosso insieme a Vite, sostituito da Bootstrap
- Metronic e Font Awesome Pro — usati nel progetto UCUE (a pagamento),
  ma non adottati qui. Per l'admin futuro si userà **Tabler**
  (gratuito) al posto di Metronic
- **AOS** (Animate on Scroll) — valutato (visto in uso in UCUE per le
  animazioni di scroll), ma escluso per ora per mantenere il progetto
  essenziale in questa fase
