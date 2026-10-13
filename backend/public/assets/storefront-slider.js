(() => {
    document.querySelectorAll('[data-hero-slider]').forEach((slider) => {
        const slides = [...slider.querySelectorAll('[data-hero-slide]')];
        const dots = [...slider.querySelectorAll('[data-slider-dot]')];
        if (slides.length < 2) return;

        let active = 0;
        let timer = null;
        const interval = Math.max(3000, Number(slider.dataset.interval) || 5000);
        const autoplay = slider.dataset.autoplay === '1' && !window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        const show = (index) => {
            active = (index + slides.length) % slides.length;
            slides.forEach((slide, slideIndex) => {
                const selected = slideIndex === active;
                slide.classList.toggle('is-active', selected);
                slide.setAttribute('aria-hidden', selected ? 'false' : 'true');
            });
            dots.forEach((dot, dotIndex) => {
                const selected = dotIndex === active;
                dot.classList.toggle('is-active', selected);
                dot.setAttribute('aria-selected', selected ? 'true' : 'false');
            });
        };

        const stop = () => {
            if (timer !== null) window.clearInterval(timer);
            timer = null;
        };
        const start = () => {
            stop();
            if (autoplay) timer = window.setInterval(() => show(active + 1), interval);
        };

        slider.querySelector('[data-slider-prev]').addEventListener('click', () => { show(active - 1); start(); });
        slider.querySelector('[data-slider-next]').addEventListener('click', () => { show(active + 1); start(); });
        dots.forEach((dot) => dot.addEventListener('click', () => { show(Number(dot.dataset.sliderDot)); start(); }));
        slider.addEventListener('mouseenter', stop);
        slider.addEventListener('mouseleave', start);
        slider.addEventListener('focusin', stop);
        slider.addEventListener('focusout', start);
        document.addEventListener('visibilitychange', () => document.hidden ? stop() : start());
        start();
    });
})();
