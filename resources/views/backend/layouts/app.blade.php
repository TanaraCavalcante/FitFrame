<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Gestione FitFrame')</title>

    {{--
        Applica tema e stato della sidebar PRIMA del paint, leggendo
        localStorage — evita un flash dello stato sbagliato (chiaro poi
        scuro, espanso poi collassato) al caricamento della pagina.
    --}}
    <script>
        (function () {
            var theme = localStorage.getItem('fitframe_admin_theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', theme);

            if (localStorage.getItem('fitframe_admin_sidebar_collapsed') === '1') {
                document.documentElement.classList.add('sidebar-collapsed');
            }
        })();
    </script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/backend.css') }}?v={{ filemtime(public_path('css/backend.css')) }}">
</head>
<body>
    <div class="d-flex backend-layout">
        <div class="backend-aside-slot">
            <aside class="backend-aside bg-dark text-white p-3">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="backend-brand-text mb-0">Gestione FitFrame</h5>
                    <button type="button" id="sidebar-toggle" class="btn btn-sm btn-outline-light border-0" aria-label="Comprimi o espandi il menu">
                        <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                        <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                    </button>
                </div>

                <nav class="nav flex-column gap-1">
                    @if (auth()->user()->isSuperAdmin())
                        <a class="nav-link text-white" href="{{ route('backend.super-admin.index') }}">
                            <i class="fa-solid fa-user-shield fa-fw" aria-hidden="true"></i>
                            <span class="backend-nav-label">Super Admin</span>
                        </a>
                        <a class="nav-link text-white" href="{{ route('backend.utenti.index') }}">
                            <i class="fa-solid fa-users fa-fw" aria-hidden="true"></i>
                            <span class="backend-nav-label">Utenti</span>
                        </a>
                        <a class="nav-link text-white" href="{{ route('backend.strutture.index') }}">
                            <i class="fa-solid fa-building fa-fw" aria-hidden="true"></i>
                            <span class="backend-nav-label">Strutture</span>
                        </a>
                    @endif
                </nav>

                <form method="POST" action="{{ route('backend.logout') }}" class="mt-4">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm w-100">
                        <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
                        <span class="backend-nav-label">Esci</span>
                    </button>
                </form>
            </aside>
        </div>

        <div class="flex-grow-1 d-flex flex-column">
            <nav class="navbar bg-body border-bottom px-3">
                <span class="navbar-text">@yield('title', 'Dashboard')</span>

                <div class="d-flex align-items-center gap-2 ms-auto">
                    <i class="fa-solid fa-sun" aria-hidden="true"></i>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" role="switch" id="theme-toggle" aria-label="Attiva tema scuro">
                    </div>
                    <i class="fa-solid fa-moon" aria-hidden="true"></i>
                </div>
            </nav>

            <main class="flex-grow-1 p-4">
                @impersonating
                    <div class="alert alert-warning d-flex justify-content-between align-items-center">
                        <span>Stai impersonando {{ auth()->user()->name }}.</span>
                        <a href="{{ route('backend.impersonate.leave') }}" class="btn btn-sm btn-dark">Torna al tuo account</a>
                    </div>
                @endImpersonating

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/backend.js') }}?v={{ filemtime(public_path('js/backend.js')) }}"></script>
</body>
</html>
