<section
    class="hero d-flex align-items-center vh-100"
    id="hero"
    style="--hero-image: url('{{ theme_url('img/hero.jpg') }}')"
>
    <div class="container">
        <span class="section-label">{{ $gym->content('hero_kicker', '— Pulse Training') }}</span>
        <h1 class="display-1">{{ $gym->content('hero_title', 'Il tuo limite è solo l’inizio.') }}</h1>
        <p class="lead">{{ $gym->content('hero_subtitle', 'Allenamenti ad alta intensità, supporto professionale e una community che ti spinge sempre avanti.') }}</p>

        <div class="d-flex gap-3 flex-wrap mt-4">
            <a href="#plans" class="btn btn-lg btn-fit-primary">{{ $gym->content('hero_cta_primary', 'Iscriviti ora') }}</a>
            <a href="#classes" class="btn btn-lg btn-fit-outline">{{ $gym->content('hero_cta_secondary', 'Scopri i corsi') }}</a>
        </div>

        <div class="mt-4">
            <span class="d-block font-heading fs-3 fw-bold text-fit-accent" data-count>{{ $gym->content('hero_stat_number', '+2000') }}</span>
            <span class="d-block small text-muted-foreground">{{ $gym->content('hero_stat_label', 'atleti attivi') }}</span>
        </div>
    </div>

    <a href="#classes" class="hero__scroll" aria-label="Scorri per vedere i corsi">
        <i class="fa-solid fa-chevron-down"></i>
    </a>
</section>
