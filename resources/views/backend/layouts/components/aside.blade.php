<div class="backend-aside-slot bg-aside border-aside">
    <aside class="backend-aside bg-aside text-aside pt-3 pb-3">
        <div class="d-flex justify-content-between align-items-center mb-4 px-3 flex-shrink-0">
            <div class="backend-brand-text mb-0">
                <img src="{{ asset('backend/img/logo.png') }}" alt="Gestione FitFrame"
                    class="backend-logo backend-logo-light">
                <img src="{{ asset('backend/img/logo-dark.png') }}" alt="Gestione FitFrame"
                    class="backend-logo backend-logo-dark">
            </div>
            <a href="#" id="sidebar-toggle" class="text-decoration-none hover-accent"
                aria-label="Comprimi o espandi il menu">
                <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
            </a>
        </div>

        <nav class="backend-aside-nav nav flex-column flex-nowrap gap-1 flex-grow-1 mb-3">
            <div class="backend-menu-heading px-3 pt-1 pb-1 text-uppercase text-gray-muted fs-8">Dashboard</div>

            <a class="nav-link backend-nav-link hover-accent @if (request()->routeIs('backend.dashboard')) active @endif"
                href="{{ route('backend.dashboard') }}">
                <i class="fa-solid fa-house me-3" aria-hidden="true"></i>
                <span class="backend-nav-label">Dashboard</span>
            </a>

            @if (auth()->user()->isSuperAdmin())
                <div class="backend-menu-heading px-3 pt-3 pb-1 text-uppercase text-gray-muted fs-8">Amministrazione
                </div>

                <a class="nav-link backend-nav-link hover-accent @if (request()->routeIs('backend.strutture.*')) active @endif"
                    href="{{ route('backend.strutture.index') }}">
                    <i class="fa-solid fa-building me-3" aria-hidden="true"></i>
                    <span class="backend-nav-label">Strutture</span>
                </a>

                <a class="nav-link backend-nav-link hover-accent @if (request()->routeIs('backend.super-admin.*')) active @endif"
                    href="{{ route('backend.super-admin.index') }}">
                    <i class="fa-solid fa-user-shield me-3" aria-hidden="true"></i>
                    <span class="backend-nav-label">Super Admin</span>
                </a>

                <a class="nav-link backend-nav-link hover-accent @if (request()->routeIs('backend.utenti.*')) active @endif"
                    href="{{ route('backend.utenti.index') }}">
                    <i class="fa-solid fa-users me-3" aria-hidden="true"></i>
                    <span class="backend-nav-label">Utenti</span>
                </a>
            @endif

            <div class="backend-menu-heading px-3 pt-3 pb-1 small text-uppercase text-gray-muted fs-8">Gestione
            </div>

            @php
                $setupChildren = [
                    ['label' => 'Hero', 'icon' => 'fa-image', 'route' => 'backend.setup.hero'],
                    ['label' => 'Team', 'icon' => 'fa-people-group', 'route' => 'backend.setup.team'],
                    ['label' => 'Galleria', 'icon' => 'fa-images', 'route' => 'backend.setup.gallery'],
                    ['label' => 'Testimonianze', 'icon' => 'fa-quote-left', 'route' => 'backend.setup.testimonials'],
                    ['label' => 'CTA', 'icon' => 'fa-bullhorn', 'route' => 'backend.setup.cta'],
                    ['label' => 'Ordina sezioni', 'icon' => 'fa-sort', 'route' => 'backend.setup.order'],
                ];
                $setupActive = request()->routeIs('backend.setup.*');
            @endphp

            <a class="nav-link backend-nav-link hover-accent d-flex align-items-center @if ($setupActive) active @endif"
                href="#setup-menu" data-bs-toggle="collapse" role="button"
                aria-expanded="{{ $setupActive ? 'true' : 'false' }}" aria-controls="setup-menu">
                <i class="fa-solid fa-gear me-3" aria-hidden="true"></i>
                <span class="backend-nav-label">Setup</span>
                <i class="fa-solid fa-chevron-down ms-auto backend-nav-label" aria-hidden="true"></i>
            </a>

            <div class="collapse @if ($setupActive) show @endif" id="setup-menu">
                <div class="nav flex-column flex-nowrap gap-1">
                    @foreach ($setupChildren as $child)
                        @if (\Illuminate\Support\Facades\Route::has($child['route']))
                            <a class="nav-link backend-nav-link ps-4 hover-accent @if (request()->routeIs($child['route'])) active @endif"
                                href="{{ route($child['route']) }}">
                                <i class="fa-solid {{ $child['icon'] }} me-3" aria-hidden="true"></i>
                                <span class="backend-nav-label">{{ $child['label'] }}</span>
                            </a>
                        @else
                            <span class="nav-link backend-nav-link ps-4 text-gray-muted d-flex align-items-center"
                                aria-disabled="true" title="Voce non disponibile: funzionalità non ancora implementata">
                                <i class="fa-solid {{ $child['icon'] }} me-3" aria-hidden="true"></i>
                                <span class="backend-nav-label">{{ $child['label'] }}</span>
                                <i class="fa-solid fa-triangle-exclamation ms-auto small backend-nav-label"
                                    aria-hidden="true"></i>
                            </span>
                        @endif
                    @endforeach
                </div>
            </div>
        </nav>

        <div class="backend-user-menu px-3 flex-shrink-0">
            <div class="backend-user-trigger d-flex align-items-center gap-2" tabindex="0">
                <span
                    class="backend-avatar rounded-circle bg-aside-subtle hover-accent">{{ auth()->user()->initials() }}</span>
                <div class="backend-nav-label backend-user-info">
                    <div class="fw-semibold text-truncate">{{ auth()->user()->name }} {{ auth()->user()->surname }}</div>
                    <div class="small text-body-secondary text-truncate">
                        @if (auth()->user()->isSuperAdmin())
                            Super Admin
                        @else
                            {{ auth()->user()->gym->name }}
                        @endif
                    </div>
                </div>
            </div>

            <div class="backend-user-popover bg-popover rounded-3 p-3">
                <div class="d-flex align-items-center gap-2 mb-4">
                    <span
                        class="backend-avatar backend-avatar-lg rounded-circle bg-aside-subtle text-aside">{{ auth()->user()->initials() }}</span>
                    <div class="backend-user-popover-meta">
                        <div class="fw-semibold text-truncate">{{ auth()->user()->name }} {{ auth()->user()->surname }}</div>
                        <div class="small text-body-secondary text-truncate">{{ auth()->user()->email }}</div>
                    </div>
                </div>

                <a href="#" class="d-block text-decoration-none rounded hover-accent mb-3 small">
                    <i class="fa-solid fa-lock text-reset me-1"></i> Cambia Password
                </a>

                <form method="POST" action="{{ route('backend.logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-back-primary text-uppercase">
                        <i class="fa-solid fa-arrow-left me-2"></i>
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </aside>
</div>
