<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label required">Nome</label>
        <input type="text" name="name" value="{{ old('name', $trainer?->name ?? '') }}" class="form-control @error('name') is-invalid @enderror">
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label required">Specialità</label>
        <input type="text" name="specialty" value="{{ old('specialty', $trainer?->specialty ?? '') }}" class="form-control @error('specialty') is-invalid @enderror" placeholder="Musculação, Yoga, Crossfit...">
        @error('specialty')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label mb-0">Foto</label>
        <p class="small text-gray-muted mb-2">Se non carichi una foto, viene usata quella di default del tema.</p>
        <div class="position-relative border rounded d-flex align-items-center justify-content-center mb-2 bg-body-tertiary" style="max-width: 160px; aspect-ratio: 3 / 4;" data-image-preview="1">
            @if ($photo)
                <img src="{{ $photo->getUrl() }}" alt="Foto {{ $trainer->name }}" class="w-100 h-100 rounded" style="object-fit: cover;">
                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 image-remove-btn" data-preview-target="1" title="Rimuovi foto">
                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                </button>
            @else
                <i class="fa-solid fa-image text-gray-muted fs-2" aria-hidden="true"></i>
            @endif
        </div>
        <input type="hidden" name="remove_photo" value="0" class="remove-image-flag" data-slot="1">
        <input type="file" name="photo" accept="image/*" class="form-control form-control-sm image-input @error('photo') is-invalid @enderror" data-preview-slot="1">
        <p class="small text-gray-muted mt-1 mb-0"><i class="fa-solid fa-info-circle me-1" aria-hidden="true"></i>Formato JPG o PNG, massimo 5MB.</p>
        @error('photo')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
