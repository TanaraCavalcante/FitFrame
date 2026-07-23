# 6. Temi

## I 3 temi

| Tema | Slug | Proposta | Peso (tipografia) |
|---|---|---|---|
| Pulse | `pulse` | *(da definire)* | *(da definire)* |
| Zenflow | `zenflow` | *(da definire)* | *(da definire)* |
| Iron House | `iron-house` | *(da definire)* | *(da definire)* |

Ognuno con identità propria (colore, tipografia/peso, logo) — la
proposta e i pesi esatti saranno definiti per ogni tema.

## Tema `base` — genitore comune

Nessuno dei 3 temi è "genitore" degli altri. Esiste un 4° tema,
`base`, che non corrisponde a nessuna palestra reale — serve solo a
contenere le view condivise. `pulse`, `zenflow` e `iron-house` sono
fratelli: tutti con `extends: base`, senza view proprie (ereditate al
100% da `base` tramite `igaster/laravel-theme`).

Motivo: se uno dei 3 fosse letteralmente la base tecnica (es. `pulse`
contenente le view reali), modificare qualcosa in `pulse` per errore
potrebbe cambiare anche `zenflow`/`iron-house` senza intenzione
chiara — mescolerebbe "brand reale" con "fondazione condivisa". Con
`base` separato, modificare una view è sempre, esplicitamente,
modificare la struttura di tutti.

## Struttura delle cartelle

```
resources/views/
  base/
    theme.json                 { "name": "base", "extends": null, "asset-path": "base" }
    home.blade.php             ← landing page, view reale, unica, condivisa
    elements/                  ← parti fisse del layout (nessun ordine, sempre presenti)
      header.blade.php
      hero.blade.php
      footer.blade.php
    sections/                 ← blocchi di contenuto ordinabili
      classes.blade.php        (ordinabile)
      plans.blade.php          (ordinabile)
      gallery.blade.php        (ordinabile)
      team.blade.php           (ordinabile)
      testimonials.blade.php   (ordinabile)
      contact-cta.blade.php    (ordinabile)

  pulse/
    theme.json                 { "name": "pulse", "extends": "base", "asset-path": "pulse" }
    (cartella view vuota — tutto risolto via fallback in base/)

  zenflow/
    theme.json                 { "name": "zenflow", "extends": "base", "asset-path": "zenflow" }
    (cartella view vuota)

  iron-house/
    theme.json                 { "name": "iron-house", "extends": "base", "asset-path": "iron-house" }
    (cartella view vuota)
```

## `elements/` vs `sections/`

`elements/` contiene le parti **fisse** della pagina — header, hero e
footer: sempre presenti, sempre nella stessa posizione, nessun
concetto di ordine. Anche se `hero` mostra contenuto legato al `gym`
(non solo struttura), la sua posizione non cambia mai, per questo vive
qui insieme a header/footer.

`sections/` contiene solo le 6 sezioni **riordinabili** tramite
`gym_sections` (vedi sezione H): `classes`, `plans`, `gallery`,
`team`, `testimonials`, `contact-cta`.

```
public/
  base/
    css/variables.css    ← palette/tipografia di fallback (solo colori/font)
    css/general.css      ← CSS strutturale condiviso (navbar, hero, container, ecc.)
    css/components.css   ← CSS dei componenti condivisi (bottoni, ecc.)
  pulse/
    css/variables.css    ← solo colori e tipografia di Pulse
    img/logo.png
    img/galeria1.jpg ... galeriaN.jpg
    team/{file}.jpg
  zenflow/
    css/variables.css
    img/logo.png
    img/galeria1.jpg ... galeriaN.jpg
    team/{file}.jpg
  iron-house/
    css/variables.css
    img/logo.png
    img/galeria1.jpg ... galeriaN.jpg
    team/{file}.jpg
```

`base` ha una cartella in `public/`, a differenza di quanto descritto
in precedenza — non viene mai servita a un dominio reale, ma contiene
il CSS strutturale/condiviso (`general.css`, `components.css`) caricato
per **tutti** i temi (vedi sezione successiva), oltre alla palette di
fallback (`variables.css`).

## `config/themes.php`

```php
return [
    'default' => 'base', // fallback usato da artisan/test, non serve mai una richiesta reale
    'themes_path' => null,
    'asset_not_found' => 'LOG_ERROR',
    'cache' => false,
    'themes' => [
        // ogni tema ha anche il proprio theme.json nella propria cartella;
        // questa lista può restare vuota se theme.json è già sufficiente
    ],
];
```

## `variables.css` — solo colori e tipografia, stesso nome per tema

Ogni tema (compreso `base`, come fallback) ha un proprio
`css/variables.css` con **solo** i token di colore e tipografia,
sempre con lo stesso nome di variabile, valore diverso:

```css
:root {
    --color-background: #0F0F0F;
    --color-background-rgb: 15, 15, 15;
    --color-surface: #1C1C1C;
    --color-foreground: #F5F5F5;
    --color-muted-foreground: #9A9A9A;
    --color-primary: #FF4438;
    --color-accent: #FFD23F;

    --font-heading: 'Oswald', sans-serif;
    --font-body: 'Inter', sans-serif;
}
```

`master.blade.php` carica **due** file di variabili, in quest'ordine —
`base/css/variables.css` (fallback) poi `theme_url('css/variables.css')`
(tema attivo, risolto dopo `Theme::set($gym->slug)`) — cosi qualunque
token non ridefinito da un tema resta comunque valido.

## `general.css` e `components.css` — struttura condivisa, non per tema

Tutto ciò che **non** è colore/tipografia (spacing, container, navbar,
hero, bottoni) vive in due file che esistono **solo** in `base/` e
vengono caricati per tutti i temi, sempre gli stessi:

- `base/css/general.css` — layout/struttura (`.container`, `.navbar`,
  `.hero`, `.section-label`, ecc.), usa Bootstrap dove possibile e CSS
  puro solo per ciò che Bootstrap non copre senza build step (blur,
  underline animato, gradiente).
- `base/css/components.css` — componenti riusabili (bottoni
  `.btn-fit-primary`/`.btn-fit-outline`, in CSS normale — non tramite
  le variabili interne `--bs-btn-*` di Bootstrap, per restare leggibili).

Entrambi leggono i token colore/tipografia via `var(--color-*)` /
`var(--font-*)` — nessun valore fisso, nessuna duplicazione per tema.
Un tema che vuole davvero un componente diverso (non solo colore/font)
resta libero di sovrascrivere queste classi nel proprio
`variables.css`, ma è l'eccezione, non la regola.

## Riferimento

Meccanismo generale (`igaster/laravel-theme`, risoluzione dominio →
tema) descritto in [3. Multi-tenant](3-multi-tenant.md). Questa doc
dettaglia la struttura concreta di cartelle/file per i 3 temi reali
del progetto.
