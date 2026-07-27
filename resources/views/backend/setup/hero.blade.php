@extends('backend.layouts.app')

@section('title', 'Hero')

@section('breadcrumb')
        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}" class="text-decoration-none hover-accent"><i class="fa-solid fa-house" aria-hidden="true"></i></a></li>
        <li class="breadcrumb-item"><span class="fs-7">Hero</span></li>
@endsection

@section('content')
    <div class="card border-0 mb-3 py-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <h1 class="h3 mb-0">Hero</h1>
                <div class="d-flex gap-2">
                    <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i>Indietro</a>
                    <button type="submit" form="hero-form" class="btn btn-sm btn-outline-success"><i class="fa-solid fa-floppy-disk me-1"></i>Salva</button>
                </div>
            </div>

            <hr class="my-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary tab-toggle-btn active" data-tab-group="hero" data-tab-target="hero-testo"><i class="fa-solid fa-align-left me-1"></i>Testo</button>
                    <button type="button" class="btn btn-sm btn-outline-primary tab-toggle-btn" data-tab-group="hero" data-tab-target="hero-visual"><i class="fa-solid fa-photo-film me-1"></i>Visual</button>
                </div>
                @if ($gyms->isNotEmpty())
                    <form method="GET" action="{{ route('backend.setup.hero') }}" class="d-flex align-items-center gap-2">
                        <label class="form-label small text-gray-muted mb-0">Struttura</label>
                        <select name="gym_id" class="form-select form-select-sm" onchange="this.form.submit()">
                            @foreach ($gyms as $g)
                                <option value="{{ $g->id }}" @selected($g->id === $gym->id)>{{ $g->name }}</option>
                            @endforeach
                        </select>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <form id="hero-form" method="POST" action="{{ route('backend.setup.hero.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <input type="hidden" name="gym_id" value="{{ $gym->id }}">
        {{-- ?Card per contenuto testuale --}}
        <div class="card border-0 mb-3" data-tab-group="hero" data-tab-panel="hero-testo">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kicker</label>
                        <input type="text" name="hero_kicker" value="{{ old('hero_kicker', $gym->content('hero_kicker')) }}" class="form-control @error('hero_kicker') is-invalid @enderror" placeholder="— Pulse Training">
                        @error('hero_kicker')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Titolo</label>
                        <input type="text" name="hero_title" value="{{ old('hero_title', $gym->content('hero_title')) }}" class="form-control @error('hero_title') is-invalid @enderror" placeholder="Il tuo limite è solo l'inizio.">
                        @error('hero_title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label">Sottotitolo</label>
                        <textarea name="hero_subtitle" rows="2" class="form-control @error('hero_subtitle') is-invalid @enderror" placeholder="Allenamenti ad alta intensità, supporto professionale e una community che ti spinge sempre avanti.">{{ old('hero_subtitle', $gym->content('hero_subtitle')) }}</textarea>
                        @error('hero_subtitle')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">CTA primario</label>
                        <input type="text" name="hero_cta_primary" value="{{ old('hero_cta_primary', $gym->content('hero_cta_primary')) }}" class="form-control @error('hero_cta_primary') is-invalid @enderror" placeholder="Iscriviti ora">
                        @error('hero_cta_primary')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">CTA secondario</label>
                        <input type="text" name="hero_cta_secondary" value="{{ old('hero_cta_secondary', $gym->content('hero_cta_secondary')) }}" class="form-control @error('hero_cta_secondary') is-invalid @enderror" placeholder="Scopri i corsi">
                        @error('hero_cta_secondary')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Numero statistica</label>
                        <input type="number" name="hero_stat_number" value="{{ old('hero_stat_number', $gym->content('hero_stat_number')) }}" class="form-control @error('hero_stat_number') is-invalid @enderror" placeholder="2000">
                        @error('hero_stat_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Etichetta statistica</label>
                        <input type="text" name="hero_stat_label" value="{{ old('hero_stat_label', $gym->content('hero_stat_label')) }}" class="form-control @error('hero_stat_label') is-invalid @enderror" placeholder="atleti attivi">
                        @error('hero_stat_label')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
        {{-- ?Card per contenuto visual --}}
        <div class="card border-0 mb-3 d-none" data-tab-group="hero" data-tab-panel="hero-visual">
            <div class="card-body">
                <p class="small text-primary mb-3">
                    <i class="fa-solid fa-circle-info me-1"></i>
                    Attivo solo ciò che è selezionato con il pulsante sopra: immagini e video sono mutuamente esclusivi.
                </p>

                <div class="form-check mb-1">
                    <input class="form-check-input visual-mode-radio" type="radio" name="visual_mode" value="images" id="visual-mode-images" @checked($visualMode !== 'video')>
                    <label class="form-check-label form-label mb-0" for="visual-mode-images">Immagini</label>
                </div>
                <p class="small text-gray-muted mb-2">Formato JPG o PNG, dimensione consigliata 1920×1080px (16:9), massimo 5MB.</p>
                <div class="row">
                    @foreach ([1, 2, 3] as $slot)
                        <div class="col-md-4 mb-3">
                            <div class="position-relative border rounded d-flex align-items-center justify-content-center mb-2 bg-body-tertiary" style="aspect-ratio: 16 / 9;" data-image-preview="{{ $slot }}">
                                @if ($images[$slot])
                                    <img src="{{ $images[$slot]->getUrl() }}" alt="Immagine hero {{ $slot }}" class="w-100 h-100 rounded" style="object-fit: cover;">
                                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 image-remove-btn" data-preview-target="{{ $slot }}" title="Rimuovi immagine">
                                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                                    </button>
                                @else
                                    <i class="fa-solid fa-image text-gray-muted fs-2" aria-hidden="true"></i>
                                @endif
                            </div>
                            <input type="hidden" name="remove_image_{{ $slot }}" value="0" class="remove-image-flag" data-slot="{{ $slot }}">
                            <input type="file" name="image_{{ $slot }}" accept="image/*" class="form-control form-control-sm image-input @error('image_'.$slot) is-invalid @enderror" data-preview-slot="{{ $slot }}">
                            @error('image_'.$slot)
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endforeach
                </div>

                <hr class="my-4">

                <div class="form-check mb-1">
                    <input class="form-check-input visual-mode-radio" type="radio" name="visual_mode" value="video" id="visual-mode-video" @checked($visualMode === 'video')>
                    <label class="form-check-label form-label mb-0" for="visual-mode-video">Video</label>
                </div>
                <p class="small text-gray-muted mb-2">Formato MP4 o MOV, massimo 50MB.</p>

                <div class="position-relative border rounded d-flex align-items-center justify-content-center mb-2 bg-body-tertiary" style="max-width: 320px; aspect-ratio: 16 / 9;" data-video-preview>
                    @if ($video)
                        <video src="{{ $video->getUrl() }}" controls class="w-100 h-100 rounded" style="object-fit: cover;"></video>
                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 video-remove-btn" title="Rimuovi video">
                            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                        </button>
                    @else
                        <i class="fa-solid fa-film text-gray-muted fs-2" aria-hidden="true"></i>
                    @endif
                </div>

                <input type="hidden" name="remove_video" value="0" class="remove-video-flag">
                <input type="file" name="video" accept="video/mp4,video/quicktime" class="form-control video-input @error('video') is-invalid @enderror">
                <div class="invalid-feedback d-none" data-video-client-error></div>
                @error('video')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </form>
@endsection
