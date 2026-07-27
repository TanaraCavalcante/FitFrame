@php
    $classesPerSlide = 4;
    $allClasses = $gym->gymClasses;
    $needsCarousel = $allClasses->count() > $classesPerSlide;
@endphp

<section id="classes" class="py-5">
    <div class="container">
        <span class="section-label">— {{ str_pad($position, 2, '0', STR_PAD_LEFT) }} / CORSI</span>
        <h2 class="font-heading text-uppercase fw-bold mb-4">{{ $gym->content('classes_title', 'Le nostre modalità') }}
        </h2>

        @if ($needsCarousel)
            <div class="classes-carousel">
                <div class="classes-viewport">
                    <div class="classes-track" data-classes-track>
                        @foreach ($allClasses as $class)
                            <div class="classes-card" data-classes-card>
                                @include('sections._class-card', ['class' => $class])
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="d-flex justify-content-center gap-3 mt-4">
                    <button type="button" class="carousel-control-prev" data-classes-prev aria-label="Precedente">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    </button>
                    <button type="button" class="carousel-control-next" data-classes-next aria-label="Successivo">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    </button>
                </div>
            </div>
        @else
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 justify-content-center">
                @foreach ($allClasses as $class)
                    <div class="col">
                        @include('sections._class-card', ['class' => $class])
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
