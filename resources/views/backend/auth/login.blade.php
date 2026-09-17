@extends('backend.layouts.guest')

@section('title', 'Accedi')

@section('form')
    <h3 class="mb-1 fw-bold text-aside">Accedi</h3>
    <p class="text-aside-muted mb-4">Inserisci le tue credenziali per continuare</p>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('backend.login') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label text-aside">Email</label>
            <div class="input-group auth-input-group">
                <span class="input-group-text"><i class="fa-solid fa-envelope" aria-hidden="true"></i></span>
                <input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required autofocus autocomplete="username">
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label text-aside">Password</label>
            <div class="input-group auth-input-group">
                <span class="input-group-text"><i class="fa-solid fa-lock" aria-hidden="true"></i></span>
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="current-password">
                <button type="button" class="btn btn-outline-secondary password-toggle" tabindex="-1" aria-label="Mostra password">
                    <i class="fa-solid fa-eye-slash" aria-hidden="true"></i>
                </button>
                @error('password')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mb-4 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="form-check">
                <input type="checkbox" name="remember" class="form-check-input" id="remember">
                <label class="form-check-label text-aside-muted" for="remember">Ricordami</label>
            </div>

            <a href="{{ route('backend.password.request') }}" class="small hover-accent">Password dimenticata?</a>
        </div>

        <button type="submit" class="btn auth-submit-btn w-100">Accedi</button>
    </form>
@endsection

@push('scripts')
    <script src="{{ asset('js/backend.js') }}?v={{ filemtime(public_path('js/backend.js')) }}"></script>
@endpush
