@php
    $features = old('features', $plan?->planFeatures->pluck('description')->all() ?? ['']);
@endphp

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label required">Nome</label>
        <input type="text" name="name" value="{{ old('name', $plan?->name ?? '') }}" class="form-control @error('name') is-invalid @enderror">
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label required">Prezzo mensile (€)</label>
        <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $plan?->price ?? '') }}" class="form-control @error('price') is-invalid @enderror">
        @error('price')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 mb-3">
        <div class="form-check">
            <input type="hidden" name="highlighted" value="0">
            <input type="checkbox" name="highlighted" value="1" id="plan-highlighted" class="form-check-input" @checked(old('highlighted', $plan?->highlighted ?? false))>
            <label class="form-check-label" for="plan-highlighted">Piano in evidenza (badge "Più popolare" — solo uno per struttura)</label>
        </div>
    </div>

    <div class="col-12 mb-3">
        <label class="form-label required">Caratteristiche</label>
        <div id="plan-features-list">
            @foreach ($features as $feature)
                <div class="input-group mb-2 plan-feature-row">
                    <input type="text" name="features[]" value="{{ $feature }}" class="form-control" placeholder="Es. Accesso illimitato alle lezioni">
                    <button type="button" class="btn btn-outline-danger plan-feature-remove" title="Rimuovi"><i class="fa-solid fa-xmark"></i></button>
                </div>
            @endforeach
        </div>
        <button type="button" class="btn btn-sm btn-outline-primary" id="plan-feature-add"><i class="fa-solid fa-plus me-1"></i>Aggiungi caratteristica</button>
        @error('features')
            <div class="text-danger small mt-2">{{ $message }}</div>
        @enderror
        @error('features.*')
            <div class="text-danger small mt-2">{{ $message }}</div>
        @enderror
    </div>
</div>
