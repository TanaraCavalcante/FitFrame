# Multi-tenant Gym Landing Page — Design

## Contexto

FitFrame vai hospedar landing pages para 3 academias diferentes na mesma
aplicação Laravel. Cada academia tem seu próprio domínio, cor primária, logo
e conteúdo (planos, equipe, depoimentos, etc), mas todas compartilham a
mesma estrutura de página (mesmas Blade views, sem duplicação).

Escopo desta primeira etapa: **páginas estáticas** (leitura de dados
já existentes no banco via seeders). Sem admin panel, sem formulário de
contato funcional, sem upload de imagem via UI — tudo isso fica para uma
etapa futura.

## A) Resolução de domínio → academia

Middleware `ResolveGym` roda em todo request:

1. Lê `request()->getHost()`.
2. Consulta a tabela `domains` pelo host (`domains.domain = 'academiaa.test'`)
   para achar o `gym_id` correspondente.
3. Carrega o `Gym` (id, name, slug) e o arquivo `config/gyms/{slug}.php`
   (cor primária, logo).
4. Compartilha ambos com as views: `view()->share('gym', $gym)` e
   `view()->share('gymTheme', $themeConfig)` (ou registra num binding
   singleton, ex: `app()->instance('currentGym', $gym)`).

Localmente, via Valet: `valet link academiaa`, `valet link academiab`,
`valet link academiac` na mesma pasta do projeto — cada um gera um
domínio próprio (`academiaa.test`, etc), todos batendo na mesma app.

## B) Cor/logo — config PHP (não fica no banco)

`config/gyms/academiaa.php`:

```php
return [
    'slug' => 'academiaa',
    'primary_color' => '#ff5500',
    'logo' => 'academiaa/logo.png',
];
```

Motivo de ficar em código (não no banco): são "design tokens" que raramente
mudam, versionados no git, sem necessidade de admin/DB pra ajustar.

## C) Schema do banco

| Tabela | Campos principais | Seção da página |
|---|---|---|
| `gyms` | id, name, slug | — (identifica a academia) |
| `domains` | id, gym_id, domain | — (resolve host → gym) |
| `gym_sections` | id, gym_id, section, order | — (ordem das seções, ver H) |
| `contacts` | id, gym_id, address, phone, whatsapp, instagram, hours | Contato/Footer |
| `contents` | id, gym_id, key, value | Textos simples (hero_headline, about_text, etc) |
| `gym_classes` | id, gym_id, name, description, icon, order | Modalidades |
| `plans` | id, gym_id, name, price, highlighted, order | Planos |
| `plan_features` | id, plan_id, description, order | Bullets de cada plano |
| `testimonials` | id, gym_id, author_name, text, order | Depoimentos |
| `personal_trainers` | id, gym_id, name, specialty, photo_path, order | Equipe |

Todas as tabelas de conteúdo (exceto `domains` e `gym_sections`, que são
estruturais) referenciam `gym_id` como foreign key direta pra `gyms.id`
— sem tabela `themes` genérica nem string solta como identificador.

`plans.features` NÃO é uma coluna JSON — é a tabela relacionada
`plan_features`, permitindo reordenar cada bullet individualmente.

Fotos de personal trainers: `personal_trainers.photo_path` guarda o
caminho do arquivo (ex: `academiaa/team/joao.jpg`), populado via seeder
nesta etapa. Upload real via UI fica pra depois.

Note que `Class` é palavra reservada no PHP — por isso a tabela/model é
`gym_classes` / `GymClass`, não `classes`/`Class`.

## D) Views (Blade)

```
resources/views/
  layouts/app.blade.php          — <head>, link do CSS do tema, @yield
  gym/
    index.blade.php              — monta header + hero + seções ordenadas + footer
    sections/
      header.blade.php           (fixo)
      hero.blade.php              (fixo)
      classes.blade.php           (modalidades — orderável)
      plans.blade.php             (orderável)
      gallery.blade.php           (orderável)
      team.blade.php              (orderável)
      testimonials.blade.php      (orderável)
      contact-cta.blade.php       (orderável)
      footer.blade.php           (fixo)
```

`GymController@index` resolve `$gym` (já disponível via middleware),
busca a ordem em `gym_sections` e passa pra `gym.index`, que:

```blade
@include('gym.sections.header')
@include('gym.sections.hero')

@foreach ($sections as $section)
    @include("gym.sections.{$section}")
@endforeach

@include('gym.sections.footer')
```

Cada partial busca seu próprio dado (via repository/service, filtrando
por `$gym->id`) — a ordem do `@foreach` é a única coisa que muda entre
academias; o conteúdo de cada seção é idêntico estruturalmente.

## E) Assets por tema (sem build step / sem Vite)

```
public/css/{slug}.css
public/images/{slug}/logo.png
public/images/{slug}/galeria1.jpg ... galeriaN.jpg
public/images/{slug}/team/{arquivo}.jpg
```

Galeria: nomes de arquivo idênticos entre temas (`galeria1.jpg`,
`galeria2.jpg`...) — o código só troca a pasta (`{slug}`) na hora de
montar o `src` da imagem. Sem tabela nem lista de config pra isso.

## F) Formulário de contato

Aparece na seção `contact-cta`, mas **sem submit funcional** nesta etapa
— apenas HTML/Blade visual. Processar o lead (salvar/enviar email) fica
para uma próxima spec.

## G) Seeders

Um seeder por academia (`AcademiaASeeder`, `AcademiaBSeeder`,
`AcademiaCSeeder`, ou um `GymSeeder` parametrizado) populando: `gyms`,
`domains`, `gym_sections` (ordem default), `contacts`, `contents`,
`gym_classes`, `plans` + `plan_features`, `testimonials`,
`personal_trainers`.

## H) Ordem das seções — `gym_sections`

Header e Hero são sempre fixos no topo; Footer sempre fixo embaixo.
As demais 6 seções são orderáveis por academia via a tabela
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

Ordem default (seed):

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

Pra uma academia querer Depoimentos antes de Planos, por exemplo, basta
ajustar o `order` das linhas dela nessa tabela (via seeder próprio ou
edição direta no banco) — sem tocar em código/views. Sem admin panel
ainda, mas a estrutura já fica pronta pra uma tela de gestão futura.

## Fora de escopo nesta etapa

- Formulário de contato funcional (salvar lead / enviar email)
- Painel admin para editar conteúdo, cor, logo, ordem das seções
- Upload de imagens via UI (personal trainers, galeria, logo)
- Deploy em produção (domínios reais)
