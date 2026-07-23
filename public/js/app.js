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
});
