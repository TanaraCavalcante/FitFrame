<section id="gallery" class="py-5">
    <div class="container">
        <span class="section-label">— {{ str_pad($position, 2, '0', STR_PAD_LEFT) }} / GALLERIA</span>
        <h2 class="font-heading text-uppercase fw-bold mb-4">{{ $gym->content('gallery_title', 'La nostra struttura') }}</h2>

        {{--
            Nessun testo pesante qui, solo immagini. In bianco e nero di default,
            a colori al passaggio del mouse (vedi .gallery-item in general.css).
            Percorsi tramite theme_url(), come logo/hero — foto vere ancora da
            aggiungere in public/pulse/img/galery1.jpg...galery5.jpg.
        --}}
        {{-- Ogni thumbnail apre la modal e salta lo slide del carousel al proprio indice (vedi js/app.js) --}}
        <div class="gallery-grid">
            @for ($i = 1; $i <= 5; $i++)
                <button
                    type="button"
                    class="gallery-item @if ($i === 1) gallery-item--large @endif"
                    style="background-image: url('{{ theme_url("img/galery{$i}.jpg") }}')"
                    data-bs-toggle="modal"
                    data-bs-target="#galleryModal"
                    data-index="{{ $i - 1 }}"
                    aria-label="{{ $gym->name }} — apri foto della struttura {{ $i }}"
                ></button>
            @endfor
        </div>
    </div>

    {{--
        Modal con carousel: si apre sullo slide della foto cliccata. Le frecce
        stanno SOTTO la foto (non ai lati) — così la foto usa tutta la
        larghezza disponibile, e le frecce restano sempre vicine tra loro,
        centrate, invece di stare lontane ai bordi del modal.
    --}}
    <div class="modal fade" id="galleryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content bg-transparent border-0">
                <div class="d-flex flex-column align-items-center gap-3">
                    <div id="galleryCarousel" class="carousel slide">
                        <div class="carousel-inner">
                            @for ($i = 1; $i <= 5; $i++)
                                <div class="carousel-item @if ($i === 1) active @endif">
                                    <img src="{{ theme_url("img/galery{$i}.jpg") }}" class="w-100 h-100 object-fit-contain d-block" alt="{{ $gym->name }} — foto della struttura {{ $i }}">
                                </div>
                            @endfor
                        </div>
                    </div>

                    <div class="d-flex gap-4">
                        <button type="button" class="btn text-muted-foreground hover-accent fs-3" data-bs-target="#galleryCarousel" data-bs-slide="prev" aria-label="Precedente">
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>

                        <button type="button" class="btn text-muted-foreground hover-accent fs-3" data-bs-target="#galleryCarousel" data-bs-slide="next" aria-label="Successivo">
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
