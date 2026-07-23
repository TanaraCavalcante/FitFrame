<section class="hero d-flex align-items-center vh-100" id="hero">
    <div class="container">
        <h1 class="display-1">{{ $gym->content('hero_title', 'Un allenamento che trasforma il tuo corpo.') }}</h1>
        <p class="lead">{{ $gym->content('hero_subtitle', 'Lezioni ad alta intensità, supporto professionale e una community che ti spinge sempre avanti.') }}</p>

        <div class="d-flex gap-3 flex-wrap mt-4">
            <a href="#plans" class="btn btn-lg btn-fit-primary">Iscriviti ora</a>
            <a href="#classes" class="btn btn-lg btn-fit-outline">Scopri i corsi</a>
        </div>
    </div>
</section>
