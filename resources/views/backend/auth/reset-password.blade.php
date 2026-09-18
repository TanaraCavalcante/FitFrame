@extends('backend.layouts.guest')

@section('title', 'Reimposta password')

@section('brand-title', 'Crea una nuova password.')

@section('brand-text', 'Scegli una password sicura per tornare ad accedere al gestionale.')

@section('form')
    <h3 class="mb-1 fw-bold text-aside">Reimposta password</h3>
    <p class="text-aside-muted mb-4">Inserisci la tua nuova password</p>

    <form method="POST" action="{{ route('backend.password.update') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div class="mb-3">
            <label class="form-label text-aside">Email</label>
            <div class="input-group auth-input-group">
                <span class="input-group-text"><i class="fa-solid fa-envelope" aria-hidden="true"></i></span>
                <input type="email" name="email" value="{{ old('email', $email) }}" class="form-control @error('email') is-invalid @enderror" required autofocus autocomplete="username">
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label text-aside">Nuova password</label>
            <div class="input-group auth-input-group">
                <span class="input-group-text"><i class="fa-solid fa-lock" aria-hidden="true"></i></span>
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password">
                <button type="button" class="btn btn-outline-secondary password-toggle" tabindex="-1" aria-label="Mostra password">
                    <i class="fa-solid fa-eye-slash" aria-hidden="true"></i>
                </button>
                @error('password')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label text-aside">Conferma password</label>
            <div class="input-group auth-input-group">
                <span class="input-group-text"><i class="fa-solid fa-lock" aria-hidden="true"></i></span>
                <input type="password" name="password_confirmation" class="form-control" required autocomplete="new-password">
                <button type="button" class="btn btn-outline-secondary password-toggle" tabindex="-1" aria-label="Mostra password">
                    <i class="fa-solid fa-eye-slash" aria-hidden="true"></i>
                </button>
            </div>
        </div>

        <button type="submit" class="btn auth-submit-btn w-100">Reimposta password</button>

        <a href="{{ route('backend.login') }}" class="d-block text-center mt-3 small hover-accent">Torna al login</a>
    </form>
@endsection

@push('scripts')
    <script src="{{ asset('js/backend.js') }}?v={{ filemtime(public_path('js/backend.js')) }}"></script>
@endpush
