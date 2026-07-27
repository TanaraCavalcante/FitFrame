<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label required">Nome</label>
        <input type="text" name="name" value="{{ old('name', $gymClass?->name ?? '') }}" class="form-control @error('name') is-invalid @enderror">
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label required">Icona</label>
        <input type="text" name="icon" value="{{ old('icon', $gymClass?->icon ?? '') }}" class="form-control @error('icon') is-invalid @enderror" placeholder="fa-solid fa-bolt">
        <div class="form-text">Classe FontAwesome completa. Elenco: <a href="https://fontawesome.com/search?ip=classic&s=solid" target="_blank" rel="noopener">fontawesome.com</a>.</div>
        @error('icon')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 mb-3">
        <label class="form-label required">Descrizione</label>
        <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $gymClass?->description ?? '') }}</textarea>
        @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
