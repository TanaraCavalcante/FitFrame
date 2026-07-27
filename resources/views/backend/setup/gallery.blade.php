@extends('backend.layouts.app')

@section('title', 'Galleria')

@section('breadcrumb')
        <li class="breadcrumb-item"><a href="{{ route('backend.dashboard') }}" class="text-decoration-none hover-accent"><i class="fa-solid fa-house" aria-hidden="true"></i></a></li>
        <li class="breadcrumb-item"><span class="fs-7">Galleria</span></li>
@endsection

@section('content')
    <div class="card border-0 mb-3 py-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <h1 class="h3 mb-0">Galleria</h1>
                <div class="d-flex gap-2">
                    <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i>Indietro</a>
                    <button type="submit" form="gallery-form" class="btn btn-sm btn-outline-success"><i class="fa-solid fa-floppy-disk me-1"></i>Salva</button>
                </div>
            </div>

            @if ($gyms->isNotEmpty())
                <hr>
                <form method="GET" action="{{ route('backend.setup.gallery') }}"
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

    <form id="gallery-form" method="POST" action="{{ route('backend.setup.gallery.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <input type="hidden" name="gym_id" value="{{ $gym->id }}">

        <div class="card border-0 p-3">
            <div class="card-body">
                {{-- Se uno slot resta vuoto, il frontend mostra la foto di default del tema per quello slot --}}
                <p class="small text-gray-muted mb-3">
                    <i class="fa-solid fa-circle-info me-1"></i>
                    Formato JPG o PNG, dimensione consigliata 1200×900px, massimo 5MB. Gli slot lasciati vuoti mostrano la foto di default del tema.
                </p>
                <div class="row">
                    @foreach ([1, 2, 3, 4, 5] as $slot)
                        <div class="col-md-4 mb-3">
                            <div class="position-relative border rounded d-flex align-items-center justify-content-center mb-2 bg-body-tertiary" style="aspect-ratio: 4 / 3;" data-image-preview="{{ $slot }}">
                                @if ($images[$slot])
                                    <img src="{{ $images[$slot]->getUrl() }}" alt="Immagine galleria {{ $slot }}" class="w-100 h-100 rounded" style="object-fit: cover;">
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
            </div>
        </div>
    </form>
@endsection
