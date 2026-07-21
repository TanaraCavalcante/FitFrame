# Multi-tenant Gym Landing Page — Design

## Contesto

FitFrame ospiterà landing page per 3 palestre diverse nella stessa
applicazione Laravel. Ogni palestra ha il proprio dominio, colore
primario, logo e contenuto (piani, staff, testimonianze, ecc.), ma tutte
condividono la stessa struttura di pagina (stesse Blade view, senza
duplicazione).

Ambito di questa prima fase: **pagine statiche** (lettura di dati già
presenti nel database tramite seeder). Nessun pannello admin, nessun
form di contatto funzionante, nessun upload di immagini via UI — tutto
questo rimane per una fase futura.

## A) Risoluzione dominio → palestra

Il middleware `ResolveGym` viene eseguito ad ogni request:

1. Legge `request()->getHost()`.
2. Interroga la tabella `domains` in base all'host
   (`domains.domain = 'academiaa.test'`) per trovare il `gym_id`
   corrispondente.
3. Carica il `Gym` (id, name, slug).
4. Chiama `Theme::set($gym->slug)` (pacchetto `igaster/laravel-theme`)
   per attivare il tema corrispondente.
5. Condivide il `Gym` con le view: `view()->share('gym', $gym)`
   (oppure registra un binding singleton, es.
   `app()->instance('currentGym', $gym)`).

In locale, tramite Valet: `valet link academiaa`, `valet link academiab`,
`valet link academiac` nella stessa cartella del progetto — ognuno
genera un proprio dominio (`academiaa.test`, ecc.), tutti sulla stessa
app.

## B) Colore/logo/tipografia — `igaster/laravel-theme` (non nel database)

Pacchetto scelto dopo analisi del progetto UCUE (dev/trabalho), che usa
lo stesso schema tema-principale + temi-figli in produzione. Ogni tema
ha un `theme.json` (in `resources/views/{slug}/theme.json`):

```json
{ "name": "academiaa", "extends": "default", "asset-path": "academiaa" }
```

L'helper `theme_url('css/variables.css')` risolve automaticamente
`public/academiaa/css/variables.css` per il tema attivo. In quel file
CSS vivono colore primario (variabili Bootstrap, vedi sezione E) e
**tipografia** — famiglia del font e pesi (regular, medium, bold,
ecc.), caricati via Google Fonts e dichiarati con variabili tipo
`--bs-font-sans-serif`. Il logo è semplicemente `theme_url('logo.png')`.

Motivo per cui restano fuori dal database: sono "design token" che
cambiano raramente, versionati su git, senza bisogno di admin/DB per
modificarli. Il pacchetto gestisce anche l'eventuale ereditarietà delle
view (`extends` nel `theme.json`) — non necessaria oggi (vedi sezione D,
view identiche per tutti i temi), ma disponibile gratuitamente se in
futuro un tema avesse bisogno di una view diversa dalle altre.

## C) Schema del database

| Tabella | Campi principali | Sezione della pagina |
|---|---|---|
| `gyms` | id, name, slug | — (identifica la palestra) |
| `domains` | id, gym_id, domain | — (risolve host → gym) |
| `gym_sections` | id, gym_id, section, order | — (ordine delle sezioni, vedi H) |
| `contacts` | id, gym_id, address, phone, whatsapp, instagram, hours | Contatto/Footer |
| `contents` | id, gym_id, key, value | Testi semplici (hero_headline, about_text, ecc.) |
| `gym_classes` | id, gym_id, name, description, icon, order | Corsi |
| `plans` | id, gym_id, name, price, highlighted, order | Piani |
| `plan_features` | id, plan_id, description, order | Bullet di ogni piano |
| `testimonials` | id, gym_id, author_name, text, order | Testimonianze |
| `personal_trainers` | id, gym_id, name, specialty, photo_path, order | Staff |

Tutte le tabelle di contenuto (eccetto `domains` e `gym_sections`, che
sono strutturali) referenziano `gym_id` come foreign key diretta verso
`gyms.id` — nessuna tabella `themes` generica né stringa sciolta come
identificatore.

`plans.features` NON è una colonna JSON — è la tabella correlata
`plan_features`, che permette di riordinare ogni bullet singolarmente.

Foto dei personal trainer: `personal_trainers.photo_path` contiene il
percorso del file (es. `academiaa/team/joao.jpg`), popolato via seeder
in questa fase. L'upload reale via UI resta per dopo.

Nota: `Class` è parola riservata in PHP — per questo la tabella/model è
`gym_classes` / `GymClass`, non `classes`/`Class`.

## D) View (Blade)

Struttura definitiva in [6. Temi](../../6-temas.md) — `base` è il tema
"genitore" (nessuna palestra reale), `pulse`/`zenflow`/`iron-house` sono
fratelli, `extends: base`, senza view proprie (ereditate al 100% da
`base` tramite `igaster/laravel-theme`):

```
resources/views/
  components/master.blade.php    — layout, <head>, CSS del tema, $slot
  base/
    theme.json                   { "name": "base", "extends": null, "asset-path": "base" }
    gym/
      index.blade.php            — assembla header + hero + sezioni ordinate + footer
    sections/
      header.blade.php           (fisso)
      hero.blade.php              (fisso)
      classes.blade.php           (corsi — ordinabile)
      plans.blade.php             (ordinabile)
      gallery.blade.php           (ordinabile)
      team.blade.php              (ordinabile)
      testimonials.blade.php      (ordinabile)
      contact-cta.blade.php       (ordinabile)
      footer.blade.php           (fisso)
  pulse/theme.json                { "name": "pulse", "extends": "base", "asset-path": "pulse" }
  zenflow/theme.json              { "name": "zenflow", "extends": "base", "asset-path": "zenflow" }
  iron-house/theme.json           { "name": "iron-house", "extends": "base", "asset-path": "iron-house" }
```

Nota: `sections/` è sorella di `gym/`, non annidata dentro — per questo
gli include sotto usano `sections.header`, non `gym.sections.header`.

`GymController@index` risolve `$gym` (già disponibile tramite
middleware), recupera l'ordine da `gym_sections` e lo passa a
`gym.index`, che:

```blade
@include('sections.header')
@include('sections.hero')

@foreach ($sections as $section)
    @include("sections.{$section}")
@endforeach

@include('sections.footer')
```

Ogni partial recupera il proprio dato (tramite repository/service,
filtrando per `$gym->id`) — l'ordine del `@foreach` è l'unica cosa che
cambia tra le palestre; il contenuto di ogni sezione è strutturalmente
identico.

## E) Asset per tema (senza build step / senza Vite)

```
public/{slug}/css/variables.css   (colori — --bs-primary, ecc., sovrascrive Bootstrap)
public/{slug}/logo.png
public/{slug}/galeria1.jpg ... galeriaN.jpg
public/{slug}/team/{file}.jpg
```

Percorsi risolti tramite `theme_url()` (`igaster/laravel-theme`), non
costruiti a mano. Galleria: nomi di file identici tra i temi
(`galeria1.jpg`, `galeria2.jpg`...) — cambia solo la cartella (`{slug}`)
risolta dal tema attivo. Nessuna tabella né lista di config per questo.

## F) Form di contatto

Compare nella sezione `contact-cta`, ma **senza submit funzionante** in
questa fase — solo HTML/Blade visivo. Elaborare il lead (salvare/
inviare email) resta per una prossima spec.

## G) Seeder

Un seeder per palestra (`AcademiaASeeder`, `AcademiaBSeeder`,
`AcademiaCSeeder`, oppure un `GymSeeder` parametrizzato) che popola:
`gyms`, `domains`, `gym_sections` (ordine di default), `contacts`,
`contents`, `gym_classes`, `plans` + `plan_features`, `testimonials`,
`personal_trainers`.

## H) Ordine delle sezioni — `gym_sections`

Header e Hero sono sempre fissi in cima; Footer sempre fisso in fondo.
Le restanti 6 sezioni sono ordinabili per palestra tramite la tabella
`gym_sections` (`gym_id`, `section`, `order`):

```php
Schema::create('gym_sections', function (Blueprint $table) {
    $table->id();
    $table->foreignId('gym_id')->constrained()->cascadeOnDelete();
    $table->string('section'); // classes, plans, gallery, team, testimonials, contact_cta
    $table->unsignedInteger('order');
    $table->timestamps();
});
```

Ordine di default (seed):

```php
$defaultOrder = ['classes', 'plans', 'gallery', 'team', 'testimonials', 'contact_cta'];

foreach ($defaultOrder as $i => $section) {
    GymSection::create([
        'gym_id' => $gym->id,
        'section' => $section,
        'order' => $i, // 0..5
    ]);
}
```

Per far sì che una palestra voglia le Testimonianze prima dei Piani, ad
esempio, basta modificare l'`order` delle sue righe in questa tabella
(tramite seeder proprio o modifica diretta nel database) — senza
toccare codice/view. Nessun pannello admin ancora, ma la struttura è già
pronta per una futura schermata di gestione.

## Fuori ambito in questa fase

- Form di contatto funzionante (salvare lead / inviare email)
- Pannello admin per modificare contenuto, colore, logo, ordine delle sezioni
- Upload di immagini via UI (personal trainer, galleria, logo)
- Deploy in produzione (domini reali)
