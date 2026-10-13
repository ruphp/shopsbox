(() => {
    const editor = document.querySelector('[data-appearance-editor]');
    const preview = editor?.querySelector('[data-appearance-preview]');
    if (!editor || !preview) {
        return;
    }

    const titleInput = editor.querySelector('[name="hero_title"]');
    const textInput = editor.querySelector('[name="hero_text"]');
    const previewTitle = preview.querySelector('[data-preview-title]');
    const previewText = preview.querySelector('[data-preview-text]');
    const storeName = preview.querySelector('header strong')?.textContent?.trim() || 'Ваш магазин';
    const previewLogo = preview.querySelector('[data-preview-logo]');
    const previewStoreName = preview.querySelector('[data-preview-store-name]');
    const showStoreNameWithLogo = editor.querySelector('[name="show_store_name_with_logo"]');
    const previewHero = preview.querySelector('[data-preview-section="hero"]');

    const updateText = () => {
        previewTitle.textContent = titleInput.value.trim() || storeName;
        previewText.textContent = textInput.value.trim() || 'Описание вашего магазина и его главного предложения.';
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

    const updateImages = () => {
        const logoUrl = editor.querySelector('[name="logo_image_id"]:checked')?.dataset.imageUrl || '';
        const heroUrl = editor.querySelector('[name="hero_image_id"]:checked')?.dataset.imageUrl || '';

        previewLogo.hidden = logoUrl === '';
        previewStoreName.hidden = logoUrl !== '' && !showStoreNameWithLogo.checked;
        if (logoUrl !== '') {
            previewLogo.src = logoUrl;
        }

        previewHero.classList.toggle('has-image', heroUrl !== '');
        if (heroUrl === '') {
            previewHero.style.removeProperty('--preview-image');
        } else {
            previewHero.style.setProperty('--preview-image', `url("${heroUrl.replaceAll('"', '%22')}")`);
        }
    };

    const updateSections = () => {
        const controls = [...editor.querySelectorAll('[data-section-control]')];
        controls
            .sort((left, right) => Number(left.querySelector('select').value) - Number(right.querySelector('select').value))
            .forEach((control) => {
                const sectionName = control.dataset.sectionControl;
                const section = preview.querySelector(`[data-preview-section="${sectionName}"]`);
                const enabled = control.querySelector('input[type="checkbox"]').checked;
                section.hidden = !enabled;
                preview.appendChild(section);
            });
    };

    titleInput.addEventListener('input', updateText);
    textInput.addEventListener('input', updateText);
    editor.querySelectorAll('[name="template"]').forEach((input) => input.addEventListener('change', updateTemplate));
    editor.querySelectorAll('[name="primary_color"]').forEach((input) => input.addEventListener('change', updateColor));
    editor.querySelectorAll('[name="logo_image_id"], [name="hero_image_id"]')
        .forEach((input) => input.addEventListener('change', updateImages));
    showStoreNameWithLogo.addEventListener('change', updateImages);
    editor.querySelectorAll('[data-section-control] input[type="checkbox"]')
        .forEach((input) => input.addEventListener('change', updateSections));

    const positionSelects = [...editor.querySelectorAll('[data-section-control] select')];
    positionSelects.forEach((select) => {
        select.dataset.previousValue = select.value;
        select.addEventListener('change', () => {
            const previousValue = select.dataset.previousValue;
            const occupied = positionSelects.find((candidate) => candidate !== select && candidate.value === select.value);
            if (occupied) {
                occupied.value = previousValue;
                occupied.dataset.previousValue = previousValue;
            }
            select.dataset.previousValue = select.value;
            updateSections();
        });
    });

    updateText();
    updateTemplate();
    updateColor();
    updateImages();
    updateSections();
})();
