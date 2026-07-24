<section id="testimonials" class="py-5">
    <div class="container">
        <span class="section-label">— {{ str_pad($position, 2, '0', STR_PAD_LEFT) }} / TESTIMONIANZE</span>
        <h2 class="font-heading text-uppercase fw-bold mb-4">
            {{ $gym->content('testimonials_title', 'Cosa dicono di noi') }}</h2>

        <div class="row row-cols-1 row-cols-lg-3 g-4">
            @foreach ($gym->testimonials as $testimonial)
                <div class="col">
                    <div class="card-fit p-4 h-100 d-flex flex-column">
                        <i class="fa-solid fa-quote-left fs-1 text-fit-primary mb-3"></i>

                        <p class="mb-4">{{ $testimonial->text }}</p>

                        <div class="mt-auto">
                            <span class="d-block font-heading fw-bold">{{ $testimonial->author_name }}</span>
                            <span class="d-block small text-muted-foreground">{{ $testimonial->member_since }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
