  <nav class="navbar bg-body border-bottom px-3">
      <span class="navbar-text">@yield('title', 'Dashboard')</span>

      <div class="d-flex align-items-center gap-2 ms-auto">
          <i class="fa-solid fa-sun" aria-hidden="true"></i>
          <div class="form-check form-switch mb-0">
              <input class="form-check-input" type="checkbox" role="switch" id="theme-toggle"
                  aria-label="Attiva tema scuro">
          </div>
          <i class="fa-solid fa-moon" aria-hidden="true"></i>
      </div>
  </nav>
