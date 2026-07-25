<div class="backend-aside-slot">
    <aside class="backend-aside p-3">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="backend-brand-text mb-0">Gestione FitFrame</h5>
            <a href="#" id="sidebar-toggle" class="backend-icon-button text-decoration-none hover-accent"
                aria-label="Comprimi o espandi il menu">
                <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
            </a>
        </div>

        <nav class="nav flex-column gap-1">
            <a class="nav-link backend-nav-link" href="{{ route('backend.dashboard') }}">
                <i class="fa-solid fa-house me-3" aria-hidden="true"></i>
                <span class="backend-nav-label ">Dashboard</span>
            </a>
        </nav>

        <form method="POST" action="{{ route('backend.logout') }}" class="mt-4">
            @csrf
            <button type="submit" class="btn btn-sm backend-outline-button w-100">
                <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
                <span class="backend-nav-label">Esci</span>
            </button>
        </form>
    </aside>
</div>
