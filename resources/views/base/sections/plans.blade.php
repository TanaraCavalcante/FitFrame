@php
    $plansPerSlide = 3;
    $allPlans = $gym->plans;
    $needsCarousel = $allPlans->count() > $plansPerSlide;
@endphp

<section id="plans" class="py-5">
    <div class="container">
        {{-- Numero dinamico: riflette la posizione reale in gym_sections per questa palestra, non un valore fisso --}}
        <span class="section-label">— {{ str_pad($position, 2, '0', STR_PAD_LEFT) }} / PIANI</span>
        <h2 class="font-heading text-uppercase fw-bold mb-4">{{ $gym->content('plans_title', 'Scegli il tuo piano') }}</h2>

        @if ($needsCarousel)
            <div class="plans-carousel carousel-strip">
                <div class="plans-viewport">
                    <div class="plans-track" data-slider-track data-slider-breakpoints='{"768":3,"0":1}'>
                        @foreach ($allPlans as $plan)
                            <div class="plans-card">
                                @include('sections._plan-card', ['plan' => $plan])
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="d-flex justify-content-center gap-3 mt-4">
                    <button type="button" class="carousel-control-prev" data-slider-prev aria-label="Precedente">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    </button>
                    <button type="button" class="carousel-control-next" data-slider-next aria-label="Successivo">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    </button>
                </div>
            </div>
        @else
            <div class="row justify-content-center g-4">
                {{-- $gym->plans arriva già ordinato per "order" (eager load con orderBy in ResolveGym) --}}
                @foreach ($allPlans as $plan)
                    <div class="col-12 col-md-4">
                        @include('sections._plan-card', ['plan' => $plan])
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
