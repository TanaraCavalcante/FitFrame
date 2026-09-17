<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accedi — Gestione FitFrame</title>
    <link rel="icon" href="{{ asset('base/img/favicon/favicon.ico') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    @foreach (['variables', 'generics', 'tipografia', 'generals', 'auth'] as $backendStylesheet)
        <link rel="stylesheet" href="{{ asset("css/backend/{$backendStylesheet}.css") }}?v={{ filemtime(public_path("css/backend/{$backendStylesheet}.css")) }}">
    @endforeach
</head>
<body>
    <div class="auth-shell">
        <div class="row g-0 min-vh-100">
            <div class="auth-brand-panel position-relative overflow-hidden text-white col-12 col-lg-5 order-2 order-lg-1 d-flex flex-column justify-content-between gap-5 p-5 p-md-5">
                <img src="{{ asset('backend/img/logo-dark.png') }}" alt="Gestione FitFrame" class="object-fit-contain w-50">

                <div class="auth-brand-content position-relative">
                    <h1 class="h2 fw-bold mb-3">Bentornato.</h1>
                    <p class="mb-0 opacity-75">Gestisci strutture, contenuti e utenti delle tue palestre da un unico pannello.</p>
                </div>

                <p class="mb-0 small opacity-50">&copy; {{ now()->year }} FitFrame</p>
            </div>

            <div class="auth-form-panel col-12 col-lg-7 order-1 order-lg-2 d-flex align-items-center justify-content-center p-5">
                <div class="auth-card w-100 border rounded-4 p-4 p-md-5">
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
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/backend.js') }}?v={{ filemtime(public_path('js/backend.js')) }}"></script>
</body>
</html>
