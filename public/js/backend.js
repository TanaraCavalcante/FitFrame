document.addEventListener('DOMContentLoaded', function () {
    var sidebarToggle = document.getElementById('sidebar-toggle');
    var themeToggle = document.getElementById('theme-toggle');

    sidebarToggle.addEventListener('click', function () {
        var collapsed = document.documentElement.classList.toggle('sidebar-collapsed');
        localStorage.setItem('fitframe_admin_sidebar_collapsed', collapsed ? '1' : '0');
    });

    themeToggle.checked = document.documentElement.getAttribute('data-bs-theme') === 'dark';

    themeToggle.addEventListener('change', function () {
        var theme = this.checked ? 'dark' : 'light';
        document.documentElement.setAttribute('data-bs-theme', theme);
        localStorage.setItem('fitframe_admin_theme', theme);
    });
});
