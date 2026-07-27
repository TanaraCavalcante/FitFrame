@php
    $heroVisualMode = $gym->content('hero_visual_mode', 'images');
    $heroVideo = $heroVisualMode === 'video' ? $gym->getFirstMedia('hero_video') : null;
    $heroImages = $heroVisualMode === 'video' ? collect() : collect([1, 2, 3])->map(fn (int $slot) => $gym->getFirstMedia("hero_image_{$slot}"))->filter();
    $heroImageUrl = $heroImages->isNotEmpty() ? $heroImages->random()->getUrl() : theme_url('img/hero.jpg');
@endphp

<section
    class="hero d-flex align-items-center @if ($heroVideo) hero--video @endif"
    id="hero"
    @unless ($heroVideo)
        style="--hero-image: url('{{ $heroImageUrl }}')"
    @endunless
>
    @if ($heroVideo)
        <video class="hero-video-bg" autoplay muted loop playsinline poster="{{ theme_url('img/hero.jpg') }}">
            <source src="{{ $heroVideo->getUrl() }}" type="{{ $heroVideo->mime_type }}">
        </video>
    @endif

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
</section>
