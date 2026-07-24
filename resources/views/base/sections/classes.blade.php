<section id="classes" class="py-5">
    <div class="container">
        <span class="section-label">— {{ str_pad($position, 2, '0', STR_PAD_LEFT) }} / CORSI</span>
        <h2 class="font-heading text-uppercase fw-bold mb-4">{{ $gym->content('classes_title', 'Le nostre modalità') }}
        </h2>

        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
            @foreach ($gym->gymClasses as $class)
                <div class="col">
                    <div class="card-fit h-100 p-4">
                        <i class="{{ $class->icon }} fs-2 text-fit-primary mb-3 d-block"></i>
                        <h3 class="font-heading fw-bold fs-5">{{ $class->name }}</h3>
                        <p class="text-muted-foreground mb-0">{{ $class->description }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
