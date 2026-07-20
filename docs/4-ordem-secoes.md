# 4. Ordine delle sezioni

## L'idea

Oltre a colore, logo e contenuto, ogni palestra può anche **cambiare
l'ordine** in cui le sezioni della pagina vengono mostrate — un
ulteriore livello di differenziazione tra i siti, senza toccare
codice/view.

## Cosa è fisso e cosa è riordinabile

- **Fissi** (sempre nella stessa posizione, per tutte le palestre):
  - `header` — sempre in cima
  - `hero` — sempre subito sotto l'header
  - `footer` — sempre in fondo
- **Riordinabili** (6 sezioni, ordine libero per palestra):
  - `classes` (Modalidades/Aulas)
  - `plans` (Planos/Preços)
  - `gallery` (Estrutura/Galeria)
  - `team` (Equipe/Personal trainers)
  - `testimonials` (Depoimentos)
  - `contact_cta` (CTA finale + Contato)

## Come viene gestito

Tabella `gym_sections` (`id`, `gym_id`, `section`, `order`):

```php
Schema::create('gym_sections', function (Blueprint $table) {
    $table->id();
    $table->foreignId('gym_id')->constrained()->cascadeOnDelete();
    $table->string('section'); // classes, plans, gallery, team, testimonials, contact_cta
    $table->unsignedInteger('order');
    $table->timestamps();
});
```

Ordine di default (uguale per tutte le palestre finché non
modificato):

```php
$defaultOrder = ['classes', 'plans', 'gallery', 'team', 'testimonials', 'contact_cta'];
// classes = order 0, plans = order 1, ... contact_cta = order 5
```

Il controller legge `gym_sections` ordinato per `order` e la view fa
un loop:

```blade
@include('gym.sections.header')
@include('gym.sections.hero')

@foreach ($sections as $section)
    @include("gym.sections.{$section}")
@endforeach

@include('gym.sections.footer')
```

## Come cambiare l'ordine oggi (senza admin)

In questa fase, senza un pannello admin, l'ordine si modifica:

- editando il seeder della palestra (cambiando i valori di `order`), oppure
- modificando direttamente le righe in `gym_sections` via Navicat/phpMyAdmin

Esempio: se `academiab` vuole le Testimonianze prima dei Piani, basta
che `testimonials.order` sia minore di `plans.order` per quella
palestra — nessuna modifica a view o controller.

## Perché una tabella e non una lista fissa in config

Tenere l'ordine nel database (invece che in un array PHP) rende la
struttura già pronta per una futura schermata di amministrazione (vedi
[5. Backend e amministrazione](5-backend-admin.md)), dove ogni
palestra potrà riordinare le proprie sezioni via drag-and-drop o simile,
senza deploy.
