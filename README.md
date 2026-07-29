# FitFrame

Piattaforma **multi-tenant** che genera landing page per palestre. Una sola
applicazione Laravel serve **più palestre diverse**, ognuna con il proprio
dominio, tema (colori/tipografia/loghi) e contenuto — tutte costruite sopra
la stessa struttura di pagina, con un pannello admin per gestire tutto senza
toccare codice.

## Indice

- [Stack](#stack)
- [Funzionalità](#funzionalità)
- [Requisiti](#requisiti)
- [Installazione](#installazione)
- [Domini locali (multi-tenant)](#domini-locali-multi-tenant)
- [Utenti di test](#utenti-di-test)
- [Migration e seeder](#migration-e-seeder)
- [Temi](#temi)
- [Test](#test)
- [Struttura del progetto](#struttura-del-progetto)
- [Documentazione](#documentazione)

## Stack

**Backend**
- PHP 8.3
- Laravel 12 (struttura file v11: middleware/provider in `bootstrap/app.php`, niente `app/Http/Kernel.php`)
- MySQL (SQLite in ambienti dove serve, vedi `.env.example`)
- [`igaster/laravel-theme`](https://github.com/igaster/laravel-theme) — risoluzione del tema attivo per dominio (`config/themes.php`, `theme.json` per tema, helper `theme_url()`)
- [`spatie/laravel-medialibrary`](https://spatie.be/docs/laravel-medialibrary) — upload immagini/video (hero, team, galleria)
- [`lab404/laravel-impersonate`](https://github.com/404labfr/laravel-impersonate) — impersonation (super_admin → gym_admin)
- Laravel Boost — linee guida/tool MCP per lo sviluppo assistito da AI (non influisce sulla produzione)

**Frontend**
- Blade puro — nessuna SPA, nessun framework JS
- Nessun bundler (Vite/Webpack) — CSS/JS serviti staticamente da `public/`, niente `npm run build`
- Bootstrap 5 (via CDN)
- Font Awesome Free (via CDN)
- Google Fonts (via CDN, una famiglia per tema)

## Funzionalità

**Sito pubblico** (una landing page per struttura, risolta per dominio)
- Header/Navbar, Hero (immagini o video), Corsi/Modalità, Piani (carousel se >3), Galleria (grid bento), Team, Testimonianze, CTA finale + Contatti, Footer
- Ordine e titolo delle sezioni personalizzabili per struttura
- Favicon e `<title>` (nome struttura) risolti per tema, con fallback al tema `base`

**Pannello admin** (dominio dedicato, `ADMIN_DOMAIN`)
- Login/logout, reset password via email, rate limiting
- Ruoli `super_admin` (gestisce tutte le strutture e gli admin) / `gym_admin` (gestisce solo la propria struttura)
- Impersonation: il super_admin può assumere l'identità di un gym_admin
- CRUD strutture (Gym) e utenti (gym_admin/super_admin)
- CRUD contenuto per sezione: Hero, Corsi, Piani (con caratteristiche e piano "in evidenza"), Team, Testimonianze, Galleria (5 slot), CTA + Contatti
- Ordina sezioni: riordino e titolo di ognuna delle 6 sezioni configurabili
- Tema chiaro/scuro persistito, sidebar collassabile, conferme di eliminazione via SweetAlert2

## Requisiti

- PHP ^8.3
- Composer
- MySQL (o SQLite per un setup rapido/locale)
- Un modo per servire domini multipli in locale — [Laravel Valet](https://laravel.com/docs/valet) (macOS/Linux) consigliato; su Windows: Laragon, Laravel Herd o WSL2 + Valet Linux

## Installazione

```bash
git clone git@github.com:TanaraCavalcante/FitFrame.git
cd FitFrame

composer install

cp .env.example .env
php artisan key:generate
```

Configura `.env` (database, `APP_URL`, `ADMIN_DOMAIN` — vedi sotto), poi:

```bash
php artisan migrate --seed
```

Il seeder crea 3 strutture di esempio con contenuto completo, i relativi domini e gli utenti admin (vedi [Migration e seeder](#migration-e-seeder)).

Avvia il progetto (server + queue listener + log in un solo comando):

```bash
composer dev
```

Oppure separatamente: `php artisan serve`, `php artisan queue:listen`, `php artisan pail`.

## Domini locali (multi-tenant)

L'app riconosce quale struttura servire in base al dominio della richiesta (tabella `domains` → `Gym`, middleware `App\Http\Middleware\ResolveGym`). Il seeder crea 3 domini:

```
pulse.test
zenflow.test
iron-house.test
```

Con Valet:

```bash
valet link pulse
valet link zenflow
valet link iron-house
```

Il pannello admin vive su un dominio a parte, configurato in `.env`:

```
ADMIN_DOMAIN=gestione.fitframe.test
```

```bash
valet link gestione.fitframe   # o il nome scelto per ADMIN_DOMAIN
```

## Utenti di test

Creati da `AdminsSeeder`:

| Ruolo | Email | Password | Struttura |
|---|---|---|---|
| super_admin | `admin@fitframe.it` | `12345678` | nessuna (accesso a tutte) |
| gym_admin | `admin@pulse.it` | `12345678` | Pulse |

## Migration e seeder

**Migration** (`database/migrations/`) — schema principale:

- `gyms` — struttura (nome, slug)
- `domains` — dominio → struttura (una struttura può avere più domini)
- `gym_sections` — ordine e titolo delle sezioni riordinabili per struttura
- `contents` — testi liberi per sezione (key/value legati al `gym_id`)
- `contacts` — dati di contatto/footer per struttura
- `gym_classes`, `plans` + `plan_features`, `testimonials`, `personal_trainers` — contenuto CRUD per sezione, con `order` per il riordino
- `media` — tabella di Spatie MediaLibrary (upload hero, team, galleria)
- `users` (esteso con `role`, `gym_id`, `surname`) — autenticazione admin

Esegui tutto con:

```bash
php artisan migrate
```

**Seeder** (`database/seeders/`):

- `GymSeeder` — popola 3 strutture demo (Pulse, Zenflow, Iron House) con dominio, contatti, ordine sezioni e contenuto completo (hero, corsi, piani, team, testimonianze)
- `AdminsSeeder` — crea gli utenti admin di test (vedi tabella sopra)
- `DatabaseSeeder` — orchestratore, richiama i due sopra in ordine

```bash
php artisan db:seed
# oppure, da zero:
php artisan migrate:fresh --seed
```

## Temi

Ogni struttura ha un tema (`igaster/laravel-theme`), attivato per slug dal middleware `ResolveGym`. Un tema può ereditare da un altro (`extends` in `theme.json`) — se un asset non esiste nel tema attivo, si risale alla catena di eredità fino a trovarlo.

```
resources/views/{slug}/theme.json   → { "name", "extends", "asset-path" }
public/{slug}/css/variables.css     → colori e tipografia (design token, non nel DB)
public/{slug}/img/...                → favicon, foto team, galleria, ecc.
```

Temi disponibili: `base` (tema principale, nessun genitore — CSS strutturale condiviso da tutti), `pulse`, `zenflow`, `iron-house` (tutti estendono `base`).

Per aggiungere un tema: crea `resources/views/{slug}/theme.json` con `"extends": "base"`, poi `public/{slug}/css/variables.css` con le variabili da sovrascrivere. Tutto il resto (asset, favicon inclusi) ricade su `base` finché non lo personalizzi.

## Test

PHPUnit (nessun Pest):

```bash
php artisan test --compact
php artisan test --compact tests/Feature/Backend/NomeDelTest.php
php artisan test --compact --filter=nomeDelTest
```

## Struttura del progetto

```
app/Http/Controllers/Backend/   Controller del pannello admin
app/Http/Middleware/ResolveGym.php   Risoluzione struttura/tema per dominio
app/Models/                     Gym, Domain, GymSection, Content, Contact,
                                 GymClass, Plan, PlanFeature, Testimonial,
                                 PersonalTrainer, User
resources/views/base/            View pubbliche condivise da tutti i temi
resources/views/{slug}/          theme.json per tema (pulse, zenflow, iron-house)
resources/views/backend/         View del pannello admin
public/base/                     CSS/JS/asset strutturali condivisi
public/{slug}/                   Asset specifici del tema
```

## Documentazione

Approfondimenti in `docs/`: stack, architettura del progetto, multi-tenant, ordine delle sezioni, pannello admin, gestione temi.
