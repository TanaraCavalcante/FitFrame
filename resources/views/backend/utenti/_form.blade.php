<div class="mb-3">
    <label class="form-label">Nome</label>
    <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" class="form-control @error('name') is-invalid @enderror">
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label">Email</label>
    <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" class="form-control @error('email') is-invalid @enderror">
    @error('email')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label">Password{{ isset($user) ? ' (lascia vuoto per non cambiarla)' : '' }}</label>
    <input type="password" name="password" value="{{ old('password', isset($user) ? '' : '12345678') }}" class="form-control @error('password') is-invalid @enderror">
    @error('password')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label">Struttura</label>
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
