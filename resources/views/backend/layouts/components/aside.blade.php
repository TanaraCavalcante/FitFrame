<div class="backend-aside-slot bg-aside border-aside">
    <aside class="backend-aside bg-aside text-aside p-3">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="backend-brand-text mb-0">
                <img src="{{ asset('backend/img/logo.png') }}" alt="Gestione FitFrame" class="backend-logo backend-logo-light">
                <img src="{{ asset('backend/img/logo-dark.png') }}" alt="Gestione FitFrame" class="backend-logo backend-logo-dark">
            </div>
            <a href="#" id="sidebar-toggle" class="text-decoration-none hover-accent"
                aria-label="Comprimi o espandi il menu">
                <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
            </a>
        </div>

        <nav class="nav flex-column gap-1">
            <a class="nav-link backend-nav-link rounded hover-accent @if(request()->routeIs('backend.dashboard')) active @endif" href="{{ route('backend.dashboard') }}">
                <i class="fa-solid fa-house me-3" aria-hidden="true"></i>
                <span class="backend-nav-label ">Dashboard</span>
            </a>
        </nav>

        <div class="backend-user-menu mt-auto">
            <div class="backend-user-trigger d-flex align-items-center gap-2" tabindex="0">
                <span class="backend-avatar rounded-circle bg-aside-subtle hover-accent">{{ auth()->user()->initials() }}</span>
                <div class="backend-nav-label backend-user-info">
                    <div class="fw-semibold text-truncate">{{ auth()->user()->name }}</div>
                    <div class="small text-body-secondary text-truncate">
                        @if (auth()->user()->isSuperAdmin())
                            {{ auth()->user()->email }}
                        @else
                            {{ auth()->user()->gym->name }}
                        @endif
                    </div>
                </div>
            </div>

            <div class="backend-user-popover bg-popover rounded-3 p-3">
                <div class="d-flex align-items-center gap-2 mb-4">
                    <span class="backend-avatar backend-avatar-lg rounded-circle bg-aside-subtle text-aside">{{ auth()->user()->initials() }}</span>
                    <div class="backend-user-popover-meta">
                        <div class="fw-semibold text-truncate">{{ auth()->user()->name }}</div>
                        <div class="small text-body-secondary text-truncate">{{ auth()->user()->email }}</div>
                    </div>
                </div>

                <a href="#" class="d-block text-decoration-none rounded hover-accent mb-3 small">Cambia Password</a>

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
