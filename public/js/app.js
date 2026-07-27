document.addEventListener('DOMContentLoaded', () => {
    AOS.init({
        duration: 700,
        once: true,
    });

    document.querySelectorAll('[data-count]').forEach((el) => {
        const target = parseInt(el.textContent.replace(/\D/g, ''), 10);

        if (!target) {
            return;
        }

        const duration = 5000;
        const start = performance.now();

        const tick = (now) => {
            const progress = Math.min((now - start) / duration, 1);
            el.textContent = '+' + Math.round(target * progress);

            if (progress < 1) {
                requestAnimationFrame(tick);
            }
        };

        requestAnimationFrame(tick);
    });

    // Striscia scorrevole (Corsi, Piani, ...): scorre di una card alla volta (in px reali), mai a blocchi interi.
    document.querySelectorAll('[data-slider-track]').forEach((track) => {
        const container = track.closest('.carousel-strip');
        const prevBtn = container.querySelector('[data-slider-prev]');
        const nextBtn = container.querySelector('[data-slider-next]');
        const cards = Array.from(track.children);
        const breakpoints = JSON.parse(track.dataset.sliderBreakpoints);

        let index = 0;

        const visibleCount = () => {
            const width = window.innerWidth;
            const match = Object.keys(breakpoints)
                .map(Number)
                .sort((a, b) => b - a)
                .find((breakpoint) => width >= breakpoint);

            return breakpoints[match] ?? 1;
        };

        const update = () => {
            const maxIndex = Math.max(cards.length - visibleCount(), 0);
            index = Math.min(index, maxIndex);

            const gap = parseFloat(getComputedStyle(track).gap) || 0;
            const step = cards[0].getBoundingClientRect().width + gap;

            track.style.transform = `translateX(-${index * step}px)`;

            prevBtn.disabled = index === 0;
            nextBtn.disabled = index >= maxIndex;
        };

        prevBtn.addEventListener('click', () => {
            index = Math.max(index - 1, 0);
            update();
        });

        nextBtn.addEventListener('click', () => {
            const maxIndex = Math.max(cards.length - visibleCount(), 0);
            index = Math.min(index + 1, maxIndex);
            update();
        });

        window.addEventListener('resize', update);

        update();
    });

    // Galleria: la modal apre sempre sulla foto cliccata, non sempre sulla prima.
    const galleryModal = document.getElementById('galleryModal');

    if (galleryModal) {
        galleryModal.addEventListener('show.bs.modal', (event) => {
            const index = parseInt(event.relatedTarget?.dataset.index ?? '0', 10);
            const carouselEl = galleryModal.querySelector('.carousel');

            bootstrap.Carousel.getOrCreateInstance(carouselEl).to(index);
        });
    }
});
