@extends('backend.layouts.guest')

@section('title', 'Password dimenticata')

@section('brand-title', "Recupera l'accesso.")

@section('brand-text', "Inserisci l'email del tuo account: ti mandiamo un link per impostare una nuova password.")

@section('form')
    <h3 class="mb-1 fw-bold text-aside">Password dimenticata</h3>
    <p class="text-aside-muted mb-4">Inserisci la tua email per ricevere il link di reset</p>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('backend.password.email') }}">
        @csrf

        <div class="mb-4">
            <label class="form-label text-aside">Email</label>
            <div class="input-group auth-input-group">
                <span class="input-group-text"><i class="fa-solid fa-envelope" aria-hidden="true"></i></span>
                <input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required autofocus autocomplete="username">
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <button type="submit" class="btn auth-submit-btn w-100">Invia link di reset</button>

        <a href="{{ route('backend.login') }}" class="d-block text-center mt-3 small hover-accent">Torna al login</a>
    </form>
@endsection
