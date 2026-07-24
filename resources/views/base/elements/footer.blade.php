<footer class="site-footer">
    <div class="container py-5">
        <div class="row">
            <div class="col-md-6">
                <a href="/"
                    class="d-block font-heading text-uppercase fw-bold text-white text-decoration-none mb-2">{{ $gym->name }}</a>
                <p class="text-muted-foreground small mb-0">
                    {{ $gym->content('footer_slogan', 'Il tuo limite è solo l’inizio.') }}</p>
            </div>

            <div class="col-md-3">
                <h6 class="text-white text-uppercase mb-3">Orari</h6>
                <p class="text-muted-foreground small mb-3">{{ $gym->contact?->hours }}</p>
                <p class="text-muted-foreground small mb-0">{{ $gym->contact?->address }}</p>
            </div>

            <div class="col-md-3">
                <div class="mb-4">
                    <h6 class="text-white text-uppercase mb-3">Contattaci</h6>
                    <ul class="list-unstyled d-flex flex-column gap-2 mb-0">
                        @if ($gym->contact?->phone)
                            <li>
                                <a href="tel:{{ $gym->contact->phone }}"
                                    class="text-muted-foreground text-decoration-none hover-accent">
                                    <i class="fa-solid fa-phone me-2"></i>{{ $gym->contact->phone }}
                                </a>
                            </li>
                        @endif

                        @if ($gym->contact?->whatsapp)
                            <li>
                                <a href="{{ $gym->contact->whatsappUrl() }}"
                                    class="text-muted-foreground text-decoration-none hover-accent">
                                    <i class="fa-brands fa-whatsapp me-2"></i>{{ $gym->contact->whatsapp }}
                                </a>
                            </li>
                        @endif
                        @if ($gym->contact?->email)
                            <li>
                                <a href="mailto:{{ $gym->contact?->email }}"
                                    class="text-muted-foreground text-decoration-none hover-accent">
                                    <i class="fa-solid fa-envelope me-2"></i>{{ $gym->contact?->email }}
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>
                @if ($gym->contact?->instagram)
                    <div>
                        <h6 class="text-white text-uppercase mb-3">Social</h6>
                        <ul class="list-unstyled d-flex flex-column gap-2 mb-0">
                            <li>
                                <a href="#" class="text-muted-foreground text-decoration-none hover-accent">
                                    <i class="fa-brands fa-instagram me-2"></i>{{ $gym->contact->instagram }}
                                </a>
                            </li>
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="footer-bottom py-3">
        <div class="container text-center">
            <span class="text-muted-foreground small">&copy; {{ now()->year }} {{ $gym->name }}. Tutti i diritti
                riservati.</span>
        </div>
    </div>
</footer>
