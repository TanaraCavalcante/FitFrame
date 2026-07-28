{{-- Griglia statica con un solo breakpoint reale (row-cols-1 / row-cols-lg-3): a differenza di Team, non serve una soglia per breakpoint, basta "più di 3" su tutte le dimensioni. --}}
@php
    $testimonialsThreshold = 3;
    $allTestimonials = $gym->testimonials;
    $needsMarquee = $allTestimonials->count() > $testimonialsThreshold;
@endphp

<section id="testimonials" class="py-5">
    <div class="container">
        <span class="section-label">— {{ str_pad($position, 2, '0', STR_PAD_LEFT) }} / TESTIMONIANZE</span>
        <h2 class="font-heading text-uppercase fw-bold mb-4">
            {{ $gym->content('testimonials_title', 'Cosa dicono di noi') }}</h2>

        @if ($needsMarquee)
            {{-- Striscia a scorrimento automatico e continuo, senza frecce (stesso meccanismo di Team) --}}
            <div class="testimonials-marquee">
                <div class="testimonials-marquee-track">
                    <div class="testimonials-marquee-group">
                        @foreach ($allTestimonials as $testimonial)
                            <div class="testimonials-marquee-card">
                                @include('sections._testimonial-card', ['testimonial' => $testimonial])
                            </div>
                        @endforeach
                    </div>
                    {{-- Copia identica, nascosta agli screen reader: crea il loop senza soluzione di continuità --}}
                    <div class="testimonials-marquee-group" aria-hidden="true">
                        @foreach ($allTestimonials as $testimonial)
                            <div class="testimonials-marquee-card">
                                @include('sections._testimonial-card', ['testimonial' => $testimonial])
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <div class="row row-cols-1 row-cols-lg-3 g-4 justify-content-center">
                @foreach ($allTestimonials as $testimonial)
                    <div class="col">
                        @include('sections._testimonial-card', ['testimonial' => $testimonial])
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
