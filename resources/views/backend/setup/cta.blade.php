@extends('backend.layouts.app')

@section('title', 'CTA')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}" class="text-decoration-none hover-accent"><i
                class="fa-solid fa-house" aria-hidden="true"></i></a></li>
    <li class="breadcrumb-item"><span class="fs-7">CTA</span></li>
@endsection

@section('content')
    <div class="card border-0 mb-3 py-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <h1 class="h3 mb-0">CTA</h1>

                <div class="d-flex gap-2">
                    <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-secondary"><i
                            class="fa-solid fa-arrow-left me-1"></i>Indietro</a>
                    <button type="submit" form="cta-form" class="btn btn-sm btn-outline-success"><i
                            class="fa-solid fa-floppy-disk me-1"></i>Salva</button>
                </div>
            </div>

            @if ($gyms->isNotEmpty())
                <hr>
                <form method="GET" action="{{ route('backend.setup.cta') }}"
                    class="d-flex justify-content-end align-items-center gap-2">
                    <label class="form-label small text-gray-muted mb-0">Struttura</label>
                    <select name="gym_id" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                        @foreach ($gyms as $g)
                            <option value="{{ $g->id }}" @selected($g->id === $gym->id)>{{ $g->name }}</option>
                        @endforeach
                    </select>
                </form>
            @endif
        </div>
    </div>

    <form id="cta-form" method="POST" action="{{ route('backend.setup.cta.update') }}">
        @csrf
        @method('PUT')
        <input type="hidden" name="gym_id" value="{{ $gym->id }}">

        <div class="card border-0">
            <div class="card-body">
                <ul class="nav nav-underline mb-4">
                    <li class="nav-item">
                        <button type="button" class="nav-link active tab-toggle-btn hover-accent" data-tab-group="cta" data-tab-target="cta-finale">
                            <i class="fa-solid fa-bullhorn me-1"></i>Cta finali
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link tab-toggle-btn hover-accent" data-tab-group="cta" data-tab-target="cta-contatti">
                            <i class="fa-solid fa-address-book me-1"></i>Contatti
                        </button>
                    </li>
                </ul>

                <div data-tab-group="cta" data-tab-panel="cta-finale">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Titolo</label>
                            <input type="text" name="cta_title" value="{{ old('cta_title', $gym->content('cta_title')) }}" class="form-control @error('cta_title') is-invalid @enderror" placeholder="Pronto a iniziare?">
                            @error('cta_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Testo pulsante</label>
                            <input type="text" name="cta_button" value="{{ old('cta_button', $gym->content('cta_button')) }}" class="form-control @error('cta_button') is-invalid @enderror" placeholder="Prenota ora">
                            @error('cta_button')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label">Sottotitolo</label>
                            <textarea name="cta_subtitle" rows="2" class="form-control @error('cta_subtitle') is-invalid @enderror" placeholder="Prenota una lezione di prova gratuita e scopri la palestra dal vivo.">{{ old('cta_subtitle', $gym->content('cta_subtitle')) }}</textarea>
                            @error('cta_subtitle')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label">Slogan footer</label>
                            <input type="text" name="footer_slogan" value="{{ old('footer_slogan', $gym->content('footer_slogan')) }}" class="form-control @error('footer_slogan') is-invalid @enderror" placeholder="Il tuo limite è solo l'inizio.">
                            @error('footer_slogan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div data-tab-group="cta" data-tab-panel="cta-contatti" class="d-none">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Indirizzo</label>
                            <input type="text" name="address" value="{{ old('address', $contact?->address ?? '') }}" class="form-control @error('address') is-invalid @enderror">
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Telefono</label>
                            <input type="text" name="phone" value="{{ old('phone', $contact?->phone ?? '') }}" class="form-control @error('phone') is-invalid @enderror">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" value="{{ old('email', $contact?->email ?? '') }}" class="form-control @error('email') is-invalid @enderror">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">WhatsApp</label>
                            <input type="text" name="whatsapp" value="{{ old('whatsapp', $contact?->whatsapp ?? '') }}" class="form-control @error('whatsapp') is-invalid @enderror">
                            @error('whatsapp')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Instagram</label>
                            <input type="text" name="instagram" value="{{ old('instagram', $contact?->instagram ?? '') }}" class="form-control @error('instagram') is-invalid @enderror" placeholder="@nomeutente">
                            @error('instagram')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Orari</label>
                            <input type="text" name="hours" value="{{ old('hours', $contact?->hours ?? '') }}" class="form-control @error('hours') is-invalid @enderror" placeholder="Lun-Ven 06h-22h, Sab 08h-14h">
                            @error('hours')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
