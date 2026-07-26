<div class="row">
    <div class="col-12 mb-3">
        <label class="form-label required">Nome</label>
        <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" class="form-control @error('name') is-invalid @enderror">
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label required">Email</label>
        <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" class="form-control @error('email') is-invalid @enderror">
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label @if (! isset($user)) required @endif">Password{{ isset($user) ? ' (lascia vuoto per non cambiarla)' : '' }}</label>
        <input type="password" name="password" value="{{ old('password', isset($user) ? '' : '12345678') }}" class="form-control @error('password') is-invalid @enderror">
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
