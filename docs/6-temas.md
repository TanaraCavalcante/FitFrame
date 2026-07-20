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
    gym/
      index.blade.php          ← view reale, unica, condivisa
    sections/
      header.blade.php
      hero.blade.php
      classes.blade.php
      plans.blade.php
      gallery.blade.php
      team.blade.php
      testimonials.blade.php
      contact-cta.blade.php
      footer.blade.php

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

```
public/
  pulse/
    css/variables.css
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

`base` non ha bisogno di una cartella in `public/` — non viene mai
servito a un dominio reale, esiste solo come fallback di view.

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

## `variables.css` — stesso nome di variabile, valore diverso per tema

Il pattern: ogni variabile (`--bs-primary`, `--bs-font-sans-serif`,
ecc.) ha **lo stesso nome** nei 3 file. Blade/CSS chiama sempre la
variabile per nome (o una classe Bootstrap come `.btn-primary`, che
usa già la variabile sotto) — mai un valore fisso. Il browser carica
solo il file del tema attivo (risolto da
`theme_url('css/variables.css')` dopo `Theme::set($gym->slug)`),
quindi non c'è cascata/ereditarietà CSS tra i 3 — ogni file è completo
e indipendente.

Esempio — `public/pulse/css/variables.css`:

```css
[data-bs-theme=light] {
    --bs-primary: #FF3D57;
    --bs-primary-rgb: 255, 61, 87;
    --bs-font-sans-serif: 'Poppins', sans-serif;
    --bs-body-font-weight: 600;
}
```

Esempio — `public/zenflow/css/variables.css`:

```css
[data-bs-theme=light] {
    --bs-primary: #5B8C6E;
    --bs-primary-rgb: 91, 140, 110;
    --bs-font-sans-serif: 'Nunito', sans-serif;
    --bs-body-font-weight: 400;
}
```

Esempio — `public/iron-house/css/variables.css`:

```css
[data-bs-theme=light] {
    --bs-primary: #1A1A1A;
    --bs-primary-rgb: 26, 26, 26;
    --bs-font-sans-serif: 'Oswald', sans-serif;
    --bs-body-font-weight: 700;
}
```

I valori sopra sono solo di esempio — colore, font e peso reali di
ogni tema saranno ancora definiti.

## Riferimento

Meccanismo generale (`igaster/laravel-theme`, risoluzione dominio →
tema) descritto in [3. Multi-tenant](3-multi-tenant.md). Questa doc
dettaglia la struttura concreta di cartelle/file per i 3 temi reali
del progetto.
