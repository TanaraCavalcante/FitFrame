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

    document.querySelectorAll('.tab-toggle-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            var group = button.dataset.tabGroup;
            var target = button.dataset.tabTarget;

            document.querySelectorAll('.tab-toggle-btn[data-tab-group="' + group + '"]').forEach(function (btn) {
                btn.classList.toggle('active', btn === button);
            });

            document.querySelectorAll('[data-tab-panel][data-tab-group="' + group + '"]').forEach(function (panel) {
                panel.classList.toggle('d-none', panel.dataset.tabPanel !== target);
            });
        });
    });

    document.addEventListener('click', function (event) {
        var imageButton = event.target.closest('.image-remove-btn');

        if (imageButton) {
            var slot = imageButton.dataset.previewTarget;
            var flag = document.querySelector('.remove-image-flag[data-slot="' + slot + '"]');
            var preview = document.querySelector('[data-image-preview="' + slot + '"]');
            var input = document.querySelector('.image-input[data-preview-slot="' + slot + '"]');

            if (flag) {
                flag.value = '1';
            }

            if (preview) {
                preview.innerHTML = '<i class="fa-solid fa-image text-gray-muted fs-2" aria-hidden="true"></i>';
            }

            if (input) {
                input.value = '';
            }

            return;
        }

        var videoButton = event.target.closest('.video-remove-btn');

        if (videoButton) {
            var videoPreview = document.querySelector('[data-video-preview]');
            var videoFlag = document.querySelector('.remove-video-flag');
            var videoFileInput = document.querySelector('.video-input');

            if (videoPreview) {
                videoPreview.innerHTML = '<i class="fa-solid fa-film text-gray-muted fs-2" aria-hidden="true"></i>';
            }

            if (videoFlag) {
                videoFlag.value = '1';
            }

            if (videoFileInput) {
                videoFileInput.value = '';
            }
        }
    });

    document.querySelectorAll('.image-input').forEach(function (input) {
        input.addEventListener('change', function () {
            var slot = input.dataset.previewSlot;
            var preview = document.querySelector('[data-image-preview="' + slot + '"]');
            var flag = document.querySelector('.remove-image-flag[data-slot="' + slot + '"]');

            if (!preview || !input.files || !input.files[0]) {
                return;
            }

            var url = URL.createObjectURL(input.files[0]);
            preview.innerHTML =
                '<img src="' + url + '" alt="Anteprima immagine" class="w-100 h-100 rounded" style="object-fit: cover;">' +
                '<button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 image-remove-btn" data-preview-target="' + slot + '" title="Rimuovi immagine"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>';

            if (flag) {
                flag.value = '0';
            }

            var imagesRadio = document.getElementById('visual-mode-images');
            if (imagesRadio) {
                imagesRadio.checked = true;
            }
        });
    });

    var videoInput = document.querySelector('.video-input');
    if (videoInput) {
        videoInput.addEventListener('change', function () {
            var clientError = document.querySelector('[data-video-client-error]');

            if (clientError) {
                clientError.textContent = '';
                clientError.classList.add('d-none');
            }
            videoInput.classList.remove('is-invalid');

            if (!videoInput.files || !videoInput.files[0]) {
                return;
            }

            try {
                var file = videoInput.files[0];
                var allowedTypes = ['video/mp4', 'video/quicktime'];
                var maxBytes = 50 * 1024 * 1024;

                if (allowedTypes.indexOf(file.type) === -1 || file.size > maxBytes) {
                    videoInput.value = '';
                    videoInput.classList.add('is-invalid');

                    if (clientError) {
                        clientError.textContent = allowedTypes.indexOf(file.type) === -1
                            ? 'Formato non valido: usa MP4 o MOV.'
                            : 'Il video supera i 50MB consentiti.';
                        clientError.classList.remove('d-none');
                    }

                    return;
                }

                var videoRadio = document.getElementById('visual-mode-video');
                if (videoRadio) {
                    videoRadio.checked = true;
                }

                var videoPreview = document.querySelector('[data-video-preview]');
                var videoFlag = document.querySelector('.remove-video-flag');

                if (videoPreview) {
                    var url = URL.createObjectURL(file);
                    videoPreview.innerHTML =
                        '<video src="' + url + '" controls class="w-100 h-100 rounded" style="object-fit: cover;"></video>' +
                        '<button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 video-remove-btn" title="Rimuovi video"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>';
                }

                if (videoFlag) {
                    videoFlag.value = '0';
                }
            } catch (error) {
                videoInput.value = '';

                if (clientError) {
                    clientError.textContent = 'Impossibile leggere il file selezionato.';
                    clientError.classList.remove('d-none');
                }
            }
        });
    }
});
