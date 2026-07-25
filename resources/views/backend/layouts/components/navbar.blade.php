  <nav class="navbar bg-aside text-aside px-3">
      <ol class="breadcrumb mb-0">
          @hasSection('breadcrumb')
              @yield('breadcrumb')
          @else
              <li class="breadcrumb-item">
                  <a href="{{ route('backend.dashboard') }}" class="text-decoration-none hover-accent">
                      <i class="fa-solid fa-house" aria-hidden="true"></i>
                  </a>
              </li>
          @endif
      </ol>

      <div class="d-flex align-items-center gap-2 ms-auto">
          <i class="fa-solid fa-sun" aria-hidden="true"></i>
          <div class="form-check form-switch mb-0">
              <input class="form-check-input" type="checkbox" role="switch" id="theme-toggle"
                  aria-label="Attiva tema scuro">
          </div>
          <i class="fa-solid fa-moon" aria-hidden="true"></i>
      </div>
  </nav>
