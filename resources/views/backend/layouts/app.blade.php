<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Gestione FitFrame')</title>

    {{-- *
         * Applica tema e stato della sidebar PRIMA del paint, leggendo
         * localStorage — evita un flash dello stato sbagliato (chiaro poi
         * scuro, espanso poi collassato) al caricamento della pagina.
    *--}}
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
    {{--?  Sidebar collassata se l'utente ha scelto di collassarla --}}
    @include('backend.layouts.components.aside')
        <div class="backend-main-shell flex-grow-1 d-flex flex-column">
        {{--?  Navbar --}}
          @include('backend.layouts.components.navbar')

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

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                @yield('content')
            </main>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/backend.js') }}?v={{ filemtime(public_path('js/backend.js')) }}"></script>
</body>
</html>
