(() => {
    document.querySelectorAll('[data-responsive-header]').forEach((header) => {
        const button = header.querySelector('[data-responsive-menu-toggle]');
        const menu = header.querySelector('[data-responsive-menu]');
        if (!button || !menu) return;

        const close = () => {
            header.classList.remove('is-menu-open');
            button.setAttribute('aria-expanded', 'false');
            button.setAttribute('aria-label', 'Открыть меню');
        };
        button.addEventListener('click', () => {
            const open = header.classList.toggle('is-menu-open');
            button.setAttribute('aria-expanded', open ? 'true' : 'false');
            button.setAttribute('aria-label', open ? 'Закрыть меню' : 'Открыть меню');
        });
        menu.addEventListener('click', (event) => {
            if (event.target.closest('a, button')) close();
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') close();
        });
        document.addEventListener('click', (event) => {
            if (!header.contains(event.target)) close();
        });
    });
})();
