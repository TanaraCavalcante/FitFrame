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

    <div class="col-12 mb-3">
        <label class="form-label required">Struttura</label>
        <select name="gym_id" class="form-select @error('gym_id') is-invalid @enderror">
            <option value="">— Seleziona —</option>
            @foreach ($gyms as $gym)
                <option value="{{ $gym->id }}" @selected((int) old('gym_id', $user->gym_id ?? '') === $gym->id)>{{ $gym->name }}</option>
            @endforeach
        </select>
        @error('gym_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
