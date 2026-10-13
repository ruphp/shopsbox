(() => {
    document.querySelectorAll('[data-product-carousel]').forEach((carousel) => {
        const track = carousel.querySelector('[data-carousel-track]');
        const previous = carousel.querySelector('[data-carousel-prev]');
        const next = carousel.querySelector('[data-carousel-next]');

        const step = () => {
            const card = track.querySelector('.product-card');
            if (!card) return track.clientWidth;
            const gap = Number.parseFloat(getComputedStyle(track).columnGap) || 0;
            return card.getBoundingClientRect().width + gap;
        };
        const update = () => {
            previous.disabled = track.scrollLeft <= 2;
            next.disabled = track.scrollLeft + track.clientWidth >= track.scrollWidth - 2;
        };

        previous.addEventListener('click', () => track.scrollBy({ left: -step(), behavior: 'smooth' }));
        next.addEventListener('click', () => track.scrollBy({ left: step(), behavior: 'smooth' }));
        track.addEventListener('scroll', update, { passive: true });
        window.addEventListener('resize', update);
        update();
    });
})();
