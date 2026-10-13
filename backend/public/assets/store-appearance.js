(() => {
    const editor = document.querySelector('[data-appearance-editor]');
    const preview = editor?.querySelector('[data-appearance-preview]');
    if (!editor || !preview) return;

    const slides = editor.querySelector('[data-hero-slides]');
    const slideTemplate = editor.querySelector('[data-slide-template]');
    const addSlide = editor.querySelector('[data-slide-add]');
    const previewHero = preview.querySelector('[data-preview-section="hero"]');
    const previewTitle = preview.querySelector('[data-preview-title]');
    const previewText = preview.querySelector('[data-preview-text]');
    const previewEyebrow = preview.querySelector('[data-preview-eyebrow]');
    const previewButton = preview.querySelector('[data-preview-button]');
    const previewLogo = preview.querySelector('[data-preview-logo]');
    const previewStoreName = preview.querySelector('[data-preview-store-name]');
    const showStoreNameWithLogo = editor.querySelector('[name="show_store_name_with_logo"]');
    const featuredTitleInput = editor.querySelector('[name="featured_title"]');
    const featuredColumnsInput = editor.querySelector('[name="featured_columns"]');
    const featuredLayoutInput = editor.querySelector('[name="featured_layout"]');
    const previewFeaturedTitle = preview.querySelector('[data-preview-featured-title]');
    const previewProductGrid = preview.querySelector('[data-preview-product-grid]');
    const featuredSourceInput = editor.querySelector('[data-featured-source]');
    const sectionOptions = editor.querySelector('.section-options');
    const slideElements = () => [...slides.querySelectorAll('[data-hero-slide]')];
    const field = (slide, name) => slide.querySelector(`[data-slide-field="${name}"]`);

    const updateHeroPreview = () => {
        const first = slideElements()[0];
        if (!first) return;
        const title = field(first, 'title').value.trim();
        const text = field(first, 'text').value.trim();
        const eyebrow = field(first, 'eyebrow').value.trim();
        const button = field(first, 'button_label').value.trim();
        const alignment = field(first, 'alignment').value;
        const overlay = Number(field(first, 'overlay').value) / 100;
        const imageUrl = field(first, 'image_id').selectedOptions[0]?.dataset.imageUrl || '';

        previewTitle.textContent = title || 'Ваш магазин';
        previewText.textContent = text || 'Описание вашего магазина и его главного предложения.';
        previewEyebrow.textContent = eyebrow;
        previewEyebrow.hidden = eyebrow === '';
        previewButton.textContent = button || 'Смотреть товары';
        previewButton.hidden = button === '';
        previewHero.classList.remove('align-left', 'align-center', 'align-right');
        previewHero.classList.add(`align-${alignment}`);
        previewHero.style.setProperty('--preview-overlay', overlay);
        previewHero.classList.toggle('has-image', imageUrl !== '');
        if (imageUrl === '') previewHero.style.removeProperty('--preview-image');
        else previewHero.style.setProperty('--preview-image', `url("${imageUrl.replaceAll('"', '%22')}")`);
    };

    const updateSlideImage = (slide) => {
        const imageUrl = field(slide, 'image_id').selectedOptions[0]?.dataset.imageUrl || '';
        const imageWrap = slide.querySelector('[data-slide-image]');
        imageWrap.hidden = imageUrl === '';
        if (imageUrl !== '') imageWrap.querySelector('img').src = imageUrl;
    };

    const renumberSlides = () => {
        const items = slideElements();
        items.forEach((slide, index) => {
            slide.querySelector('[data-slide-number]').textContent = `Слайд ${index + 1}`;
            slide.querySelectorAll('[name]').forEach((input) => {
                input.name = input.name.replace(/hero_slides\[\d+]/, `hero_slides[${index}]`);
            });
            slide.querySelector('[data-slide-up]').disabled = index === 0;
            slide.querySelector('[data-slide-down]').disabled = index === items.length - 1;
            slide.querySelector('[data-slide-remove]').disabled = items.length === 1;
        });
        addSlide.disabled = items.length >= 5;
        addSlide.textContent = items.length >= 5 ? 'Добавлено максимум 5 слайдов' : 'Добавить слайд';
        updateHeroPreview();
    };

    const updateProductsPreview = () => {
        previewFeaturedTitle.textContent = featuredTitleInput.value.trim() || 'Популярное';
        const columns = Number(featuredColumnsInput.value);
        previewProductGrid.style.setProperty('--preview-columns', columns);
        previewProductGrid.replaceChildren(...Array.from({ length: columns }, () => document.createElement('i')));
        previewProductGrid.classList.toggle('is-carousel', featuredLayoutInput.value === 'carousel');
    };

    const updateFeaturedSource = () => {
        editor.querySelectorAll('[data-featured-source-panel]').forEach((panel) => {
            panel.hidden = panel.dataset.featuredSourcePanel !== featuredSourceInput.value;
        });
    };

    const updateTemplate = () => {
        const selected = editor.querySelector('[name="template"]:checked')?.value || 'classic';
        preview.classList.remove('template-classic', 'template-catalog', 'template-accent');
        preview.classList.add(`template-${selected}`);
    };

    const updateColor = () => {
        const selected = editor.querySelector('[name="primary_color"]:checked')?.value || '#0077b6';
        preview.style.setProperty('--preview-accent', selected);
    };

    const updateLogo = () => {
        const logoUrl = editor.querySelector('[name="logo_image_id"]:checked')?.dataset.imageUrl || '';
        previewLogo.hidden = logoUrl === '';
        previewStoreName.hidden = logoUrl !== '' && !showStoreNameWithLogo.checked;
        if (logoUrl !== '') previewLogo.src = logoUrl;
    };

    const updateSections = () => {
        [...sectionOptions.querySelectorAll('[data-section-control]')]
            .forEach((control, index, controls) => {
                control.querySelector('.section-position').value = String(index + 1);
                control.querySelector('[data-section-up]').disabled = index === 0;
                control.querySelector('[data-section-down]').disabled = index === controls.length - 1;
                const section = preview.querySelector(`[data-preview-section="${control.dataset.sectionControl}"]`);
                section.hidden = !control.querySelector('input[type="checkbox"]').checked;
                preview.appendChild(section);
            });
    };

    editor.addEventListener('input', (event) => {
        if (event.target.closest('[data-hero-slide]')) updateHeroPreview();
        if (event.target === featuredTitleInput) updateProductsPreview();
    });

    editor.addEventListener('change', (event) => {
        const slide = event.target.closest('[data-hero-slide]');
        if (!slide) return;
        if (event.target.matches('[data-slide-field="image_id"]')) updateSlideImage(slide);
        updateHeroPreview();
    });

    editor.addEventListener('click', (event) => {
        const sectionAction = event.target.closest('[data-section-up], [data-section-down]');
        if (sectionAction) {
            const control = sectionAction.closest('[data-section-control]');
            if (sectionAction.matches('[data-section-up]') && control.previousElementSibling) {
                sectionOptions.insertBefore(control, control.previousElementSibling);
            } else if (sectionAction.matches('[data-section-down]') && control.nextElementSibling) {
                sectionOptions.insertBefore(control.nextElementSibling, control);
            }
            updateSections();
            return;
        }
        const action = event.target.closest('[data-slide-up], [data-slide-down], [data-slide-remove]');
        if (!action) return;
        const slide = action.closest('[data-hero-slide]');
        if (action.matches('[data-slide-up]') && slide.previousElementSibling) {
            slides.insertBefore(slide, slide.previousElementSibling);
        } else if (action.matches('[data-slide-down]') && slide.nextElementSibling) {
            slides.insertBefore(slide.nextElementSibling, slide);
        } else if (action.matches('[data-slide-remove]') && slideElements().length > 1) {
            slide.remove();
        }
        renumberSlides();
    });

    addSlide.addEventListener('click', () => {
        if (slideElements().length >= 5) return;
        const slide = slideTemplate.content.firstElementChild.cloneNode(true);
        slides.appendChild(slide);
        updateSlideImage(slide);
        renumberSlides();
        slide.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });

    editor.querySelectorAll('[name="template"]').forEach((input) => input.addEventListener('change', updateTemplate));
    editor.querySelectorAll('[name="primary_color"]').forEach((input) => input.addEventListener('change', updateColor));
    editor.querySelectorAll('[name="logo_image_id"]').forEach((input) => input.addEventListener('change', updateLogo));
    showStoreNameWithLogo.addEventListener('change', updateLogo);
    featuredColumnsInput.addEventListener('change', updateProductsPreview);
    featuredLayoutInput.addEventListener('change', updateProductsPreview);
    featuredSourceInput.addEventListener('change', updateFeaturedSource);
    editor.querySelectorAll('[data-section-control] input[type="checkbox"]')
        .forEach((input) => input.addEventListener('change', updateSections));

    const orderedControls = [...sectionOptions.querySelectorAll('[data-section-control]')]
        .sort((left, right) => Number(left.querySelector('.section-position').value) - Number(right.querySelector('.section-position').value));
    orderedControls.forEach((control) => sectionOptions.appendChild(control));

    sectionOptions.addEventListener('dragstart', (event) => {
        if (!event.target.matches('.section-drag-handle')) return;
        event.target.closest('[data-section-control]').classList.add('is-dragging');
    });
    sectionOptions.addEventListener('dragend', () => {
        sectionOptions.querySelector('.is-dragging')?.classList.remove('is-dragging');
        updateSections();
    });
    sectionOptions.addEventListener('dragover', (event) => {
        const dragging = sectionOptions.querySelector('.is-dragging');
        if (!dragging) return;
        event.preventDefault();
        const target = event.target.closest('[data-section-control]');
        if (!target || target === dragging) return;
        const bounds = target.getBoundingClientRect();
        sectionOptions.insertBefore(dragging, event.clientY < bounds.top + bounds.height / 2 ? target : target.nextElementSibling);
    });

    slideElements().forEach(updateSlideImage);
    renumberSlides();
    updateProductsPreview();
    updateFeaturedSource();
    updateTemplate();
    updateColor();
    updateLogo();
    updateSections();
})();
