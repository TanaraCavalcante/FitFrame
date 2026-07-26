<div class="row">
    <div class="col-12 mb-3">
        <label class="form-label required">Nome</label>
        <input type="text" name="name" value="{{ old('name', $gym?->name ?? '') }}" class="form-control @error('name') is-invalid @enderror">
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label required">Tema (slug)</label>
        <input type="text" name="slug" value="{{ old('slug', $gym?->slug ?? 'base') }}" class="form-control @error('slug') is-invalid @enderror">
        @error('slug')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label required">Dominio</label>
        <input type="text" name="domain" value="{{ old('domain', $gym?->domains->first()?->domain ?? '') }}" class="form-control @error('domain') is-invalid @enderror" placeholder="esempio.test">
        @error('domain')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
