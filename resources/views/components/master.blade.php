<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name'))</title>

    {{-- Bootstrap 5.3 via CDN (senza npm/vite) --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    {{-- Font Awesome 6 via CDN, per le icone --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    {{-- AOS (Animate On Scroll) via CDN, per le animazioni all'ingresso in viewport --}}
    <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">

    {{--
        Cache-busting con filemtime(): senza build step/versioning, il browser
        cacheggia questi CSS in modo aggressivo — un ?v= che cambia ad ogni
        modifica del file forza il ricaricamento, senza bisogno di hard-refresh.
    --}}

    {{-- Palette/tipografia di fallback (base), poi quelle del tema attivo le sovrascrivono --}}
    <link rel="stylesheet" href="{{ asset('base/css/variables.css') }}?v={{ filemtime(public_path('base/css/variables.css')) }}">
    <link rel="stylesheet" href="{{ theme_url('css/variables.css') }}">

    {{-- Classi utility di colore/tipografia, una per token — esiste solo nel tema base --}}
    <link rel="stylesheet" href="{{ asset('base/css/generics.css') }}?v={{ filemtime(public_path('base/css/generics.css')) }}">

    {{-- CSS strutturale e componenti condivisi, esistono solo nel tema base, uguali per tutti i temi --}}
    <link rel="stylesheet" href="{{ asset('base/css/general.css') }}?v={{ filemtime(public_path('base/css/general.css')) }}">
    <link rel="stylesheet" href="{{ asset('base/css/components.css') }}?v={{ filemtime(public_path('base/css/components.css')) }}">

    {{ $css ?? '' }}
</head>
<body>
    {{ $slot }}

    {{-- Bootstrap JS (dropdown, modal, ecc.) — prima del JS del progetto, che puo dipenderne --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    {{-- AOS JS, inizializzato in js/app.js --}}
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <script src="{{ asset('js/app.js') }}?v={{ filemtime(public_path('js/app.js')) }}"></script>

    {{ $script ?? '' }}
</body>
</html>
