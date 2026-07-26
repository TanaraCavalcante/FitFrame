document.addEventListener('DOMContentLoaded', function () {
    var sidebarToggle = document.getElementById('sidebar-toggle');
    var themeToggle = document.getElementById('theme-toggle');
    var sidebarSlot = document.querySelector('.backend-aside-slot');

    sidebarToggle.addEventListener('click', function () {
        document.documentElement.classList.remove('sidebar-hovering');
        var collapsed = document.documentElement.classList.toggle('sidebar-collapsed');
        localStorage.setItem('fitframe_admin_sidebar_collapsed', collapsed ? '1' : '0');
    });

    sidebarSlot.addEventListener('mouseenter', function () {
        if (document.documentElement.classList.contains('sidebar-collapsed')) {
            document.documentElement.classList.add('sidebar-hovering');
        }
    });

    sidebarSlot.addEventListener('mouseleave', function () {
        document.documentElement.classList.remove('sidebar-hovering');
    });

    themeToggle.checked = document.documentElement.getAttribute('data-bs-theme') === 'dark';

    themeToggle.addEventListener('change', function () {
        var theme = this.checked ? 'dark' : 'light';
        document.documentElement.setAttribute('data-bs-theme', theme);
        localStorage.setItem('fitframe_admin_theme', theme);
    });

    document.querySelectorAll('.password-toggle').forEach(function (button) {
        button.addEventListener('click', function () {
            var input = button.closest('.input-group').querySelector('input');
            var icon = button.querySelector('i');
            var willShow = input.type === 'password';

            input.type = willShow ? 'text' : 'password';
            icon.classList.toggle('fa-eye', willShow);
            icon.classList.toggle('fa-eye-slash', !willShow);
            button.setAttribute('aria-label', willShow ? 'Nascondi password' : 'Mostra password');
        });
    });
});
