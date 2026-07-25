<div class="mb-3">
    <label class="form-label">Nome</label>
    <input type="text" name="name" value="{{ old('name', $gym?->name ?? '') }}" class="form-control @error('name') is-invalid @enderror">
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label">Tema</label>
    <select name="slug" class="form-select @error('slug') is-invalid @enderror">
        <option value="">— Seleziona —</option>
        @foreach ($themes as $theme)
            <option value="{{ $theme }}" @selected(old('slug', $gym?->slug ?? '') === $theme)>{{ $theme }}</option>
        @endforeach
    </select>
    @error('slug')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label">Dominio</label>
    <input type="text" name="domain" value="{{ old('domain', $gym?->domains->first()?->domain ?? '') }}" class="form-control @error('domain') is-invalid @enderror" placeholder="esempio.test">
    @error('domain')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
