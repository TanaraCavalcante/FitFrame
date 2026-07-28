<div class="card-fit p-4 h-100 d-flex flex-column">
    <i class="fa-solid fa-quote-left fs-1 text-fit-primary mb-3"></i>

    <p class="mb-4">{{ $testimonial->text }}</p>

    <div class="mt-auto">
        <span class="d-block font-heading fw-bold">{{ $testimonial->author_name }}</span>
        <span class="d-block small text-muted-foreground">{{ $testimonial->member_since }}</span>
    </div>
</div>
