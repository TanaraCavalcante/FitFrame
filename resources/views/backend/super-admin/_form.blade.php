<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label required">Nome</label>
        <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" class="form-control @error('name') is-invalid @enderror">
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label required">Cognome</label>
        <input type="text" name="surname" value="{{ old('surname', $user->surname ?? '') }}" class="form-control @error('surname') is-invalid @enderror">
        @error('surname')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label required">Email</label>
        <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" autocomplete="off" class="form-control @error('email') is-invalid @enderror">
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label @if (! isset($user)) required @endif">Password{{ isset($user) ? ' (lascia vuoto per non cambiarla)' : '' }}</label>
        <div class="input-group">
            <input type="password" name="password" value="{{ old('password') }}" autocomplete="new-password" class="form-control @error('password') is-invalid @enderror">
            <button type="button" class="btn btn-outline-secondary password-toggle" tabindex="-1" aria-label="Mostra password">
                <i class="fa-solid fa-eye-slash" aria-hidden="true"></i>
            </button>
            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>
