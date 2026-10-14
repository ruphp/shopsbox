(() => {
    document.querySelectorAll('[data-product-carousel]').forEach((carousel) => {
        const track = carousel.querySelector('[data-carousel-track]');
        const previous = carousel.querySelector('[data-carousel-prev]');
        const next = carousel.querySelector('[data-carousel-next]');
        const interval = Math.max(3000, Number(carousel.dataset.interval) || 5000);
        const autoplay = carousel.dataset.autoplay === '1'
            && !window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        let timer = null;

        const step = () => {
            const card = track.querySelector('.product-card');
            if (!card) return track.clientWidth;
            const gap = Number.parseFloat(getComputedStyle(track).columnGap) || 0;
            return card.getBoundingClientRect().width + gap;
        };
        const hasOverflow = () => track.scrollWidth > track.clientWidth + 2;
        const atStart = () => track.scrollLeft <= 2;
        const atEnd = () => track.scrollLeft + track.clientWidth >= track.scrollWidth - 2;
        const update = () => {
            const enabled = hasOverflow();
            previous.disabled = !enabled;
            next.disabled = !enabled;
            carousel.classList.toggle('has-overflow', enabled);
        };
        const move = (direction) => {
            if (!hasOverflow()) return;
            if (direction > 0 && atEnd()) {
                track.scrollTo({ left: 0, behavior: 'smooth' });
                return;
            }
            if (direction < 0 && atStart()) {
                track.scrollTo({ left: track.scrollWidth, behavior: 'smooth' });
                return;
            }
            track.scrollBy({ left: direction * step(), behavior: 'smooth' });
        };
        const stop = () => {
            if (timer !== null) window.clearInterval(timer);
            timer = null;
        };
        const start = () => {
            stop();
            if (autoplay && hasOverflow() && !document.hidden) {
                timer = window.setInterval(() => move(1), interval);
            }
        };
        const restart = () => {
            stop();
            start();
        };

        previous.addEventListener('click', () => { move(-1); restart(); });
        next.addEventListener('click', () => { move(1); restart(); });
        track.addEventListener('scroll', update, { passive: true });
        carousel.addEventListener('mouseenter', stop);
        carousel.addEventListener('mouseleave', start);
        carousel.addEventListener('focusin', stop);
        carousel.addEventListener('focusout', start);
        carousel.addEventListener('pointerdown', stop);
        carousel.addEventListener('pointerup', start);
        document.addEventListener('visibilitychange', () => document.hidden ? stop() : start());
        window.addEventListener('resize', () => { update(); start(); });
        update();
        start();
    });
})();
