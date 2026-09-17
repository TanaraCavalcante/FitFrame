<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') — Gestione FitFrame</title>
    <link rel="icon" href="{{ asset('base/img/favicon/favicon.ico') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    @foreach (['variables', 'generics', 'tipografia', 'generals', 'auth'] as $backendStylesheet)
        @php($backendStylesheetPath = "css/backend/{$backendStylesheet}.css")
        <link rel="stylesheet" href="{{ asset($backendStylesheetPath) }}?v={{ filemtime(public_path($backendStylesheetPath)) }}">
    @endforeach
</head>
<body>
    <div class="auth-shell">
        <div class="row g-0 min-vh-100">
            <div class="auth-brand-panel position-relative overflow-hidden text-white col-12 col-lg-5 order-2 order-lg-1 d-flex flex-column justify-content-between gap-5 p-5 p-md-5">
                <img src="{{ asset('backend/img/logo-dark.png') }}" alt="Gestione FitFrame" class="object-fit-contain w-50">

                <div class="auth-brand-content position-relative">
                    <h1 class="h2 fw-bold mb-3">@yield('brand-title', 'Bentornato.')</h1>
                    <p class="mb-0 opacity-75">@yield('brand-text', 'Gestisci strutture, contenuti e utenti delle tue palestre da un unico pannello.')</p>
                </div>

                <p class="mb-0 small opacity-50">&copy; {{ now()->year }} FitFrame</p>
            </div>

            <div class="auth-form-panel col-12 col-lg-7 order-1 order-lg-2 d-flex align-items-center justify-content-center p-5">
                <div class="auth-card w-100 border rounded-4 p-4 p-md-5">
                    @yield('form')
                </div>
            </div>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
