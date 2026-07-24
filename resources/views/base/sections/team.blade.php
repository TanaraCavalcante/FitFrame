<section id="team" class="py-5 bg-surface">
    <div class="container">
        <span class="section-label">— {{ str_pad($position, 2, '0', STR_PAD_LEFT) }} / TEAM</span>
        <h2 class="font-heading text-uppercase fw-bold mb-4">{{ $gym->content('team_title', 'Il nostro team') }}</h2>

        <div class="row row-cols-2 row-cols-lg-4 g-4">
            @foreach ($gym->personalTrainers as $trainer)
                <div class="col">
                    <div class="team-card">
                        <img src="{{ asset($trainer->photo_path) }}" class="w-100 h-100 object-fit-cover" alt="{{ $trainer->name }}">

                        <div class="team-card__overlay">
                            <span class="d-block font-heading text-uppercase fw-bold">{{ $trainer->name }}</span>
                            <span class="d-block small text-muted-foreground">{{ $trainer->specialty }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
