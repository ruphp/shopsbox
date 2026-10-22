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
    const previewSecondaryButton = preview.querySelector('[data-preview-secondary-button]');
    const previewSlideSwitcher = preview.querySelector('[data-preview-slide-switcher]');
    const previewSlideNav = preview.querySelector('[data-preview-slide-nav]');
    const previewLogo = preview.querySelector('[data-preview-logo]');
    const previewStoreName = preview.querySelector('[data-preview-store-name]');
    const showStoreNameWithLogo = editor.querySelector('[name="show_store_name_with_logo"]');
    const productBlockEditors = editor.querySelector('[data-product-block-editors]');
    const productBlockTemplate = editor.querySelector('[data-product-block-template]');
    const productPreviewTemplate = editor.querySelector('[data-product-preview-template]');
    const sectionOptions = editor.querySelector('.section-options');
    const blockLibrary = editor.querySelector('[data-preview-block-library]');
    const previewStage = editor.querySelector('[data-preview-stage]');
    const blockDialog = editor.querySelector('[data-block-library-dialog]');
    const mediaPickerDialog = editor.querySelector('[data-media-picker-dialog]');
    const editorTabs = [...editor.querySelectorAll('[data-editor-tab]')];
    const sectionControl = (name) => sectionOptions.querySelector(`[data-section-control="${name}"]`);
    const sectionEnabled = (control) => control.querySelector('input[type="checkbox"]').checked;
    const slideElements = () => [...slides.querySelectorAll('[data-hero-slide]')];
    const field = (slide, name) => slide.querySelector(`[data-slide-field="${name}"]`);
    const previewSlideElements = () => slideElements().filter((slide) => field(slide, 'title').value.trim() !== '');
    let activeSlide = slideElements()[0] || null;
    let mediaPickerTarget = null;

    const mediaInfo = (value) => {
        const option = [...mediaPickerDialog.querySelectorAll('[data-media-option]')]
            .find((item) => item.dataset.mediaValue === value);
        return {
            url: option?.dataset.mediaUrl || '',
            system: option?.dataset.mediaSystem === '1',
        };
    };

    const setSlideExpanded = (slide, expanded) => {
        slide.querySelector('[data-slide-body]').hidden = !expanded;
        slide.querySelector('[data-slide-select]').setAttribute('aria-expanded', String(expanded));
    };

    const selectSlide = (slide, expand = false) => {
        if (!slide) return;
        activeSlide = slide;
        slideElements().forEach((item) => {
            item.classList.toggle('is-active', item === slide);
            if (expand) setSlideExpanded(item, item === slide);
        });
        updateHeroPreview();
    };

    const updateHeroPreview = () => {
        const slide = activeSlide && slides.contains(activeSlide) ? activeSlide : slideElements()[0];
        if (!slide) return;
        activeSlide = slide;
        const title = field(slide, 'title').value.trim();
        const text = field(slide, 'text').value.trim();
        const eyebrow = field(slide, 'eyebrow').value.trim();
        const button = field(slide, 'button_label').value.trim();
        const secondaryButton = field(slide, 'secondary_label').value.trim();
        const alignment = field(slide, 'alignment').value;
        const verticalAlignment = field(slide, 'vertical_alignment').value;
        const contentWidth = field(slide, 'content_width').value;
        const imagePosition = field(slide, 'image_position').value;
        const height = field(slide, 'height').value;
        const textTone = field(slide, 'text_tone').value;
        const buttonStyle = field(slide, 'button_style').value;
        const contentOrder = field(slide, 'content_order').value;
        const overlay = Number(field(slide, 'overlay').value) / 100;
        const imageUrl = field(slide, 'image_id').selectedOptions[0]?.dataset.imageUrl || '';

        previewTitle.textContent = title || 'Ваш магазин';
        previewText.textContent = text || 'Описание вашего магазина и его главного предложения.';
        previewEyebrow.textContent = eyebrow;
        previewEyebrow.hidden = eyebrow === '';
        previewButton.textContent = button || 'Смотреть товары';
        previewButton.hidden = button === '';
        previewSecondaryButton.textContent = secondaryButton;
        previewSecondaryButton.hidden = secondaryButton === '';
        previewHero.classList.remove('align-left', 'align-center', 'align-right');
        previewHero.classList.add(`align-${alignment}`);
        previewHero.classList.remove('vertical-top', 'vertical-center', 'vertical-bottom');
        previewHero.classList.add(`vertical-${verticalAlignment}`);
        previewHero.classList.remove('content-narrow', 'content-medium', 'content-wide');
        previewHero.classList.add(`content-${contentWidth}`);
        previewHero.classList.remove('height-compact', 'height-regular', 'height-tall');
        previewHero.classList.add(`height-${height}`);
        previewHero.classList.remove('tone-auto', 'tone-light', 'tone-dark');
        previewHero.classList.add(`tone-${textTone}`);
        previewHero.classList.remove('buttons-accent', 'buttons-light', 'buttons-outline');
        previewHero.classList.add(`buttons-${buttonStyle}`);
        previewHero.classList.remove('order-text-first', 'order-buttons-first');
        previewHero.classList.add(`order-${contentOrder}`);
        previewHero.style.setProperty('--preview-overlay', overlay);
        previewHero.style.setProperty('--preview-image-position', imagePosition);
        previewHero.classList.toggle('has-image', imageUrl !== '');
        if (imageUrl === '') previewHero.style.removeProperty('--preview-image');
        else previewHero.style.setProperty('--preview-image', `url("${imageUrl.replaceAll('"', '%22')}")`);
        previewSlideElements().forEach((item, index) => {
            const dot = previewSlideNav.children[index];
            if (!dot) return;
            const active = item === slide;
            dot.classList.toggle('is-active', active);
            dot.setAttribute('aria-pressed', String(active));
        });
    };

    const updateSlideImage = (slide) => {
        const imageUrl = field(slide, 'image_id').selectedOptions[0]?.dataset.imageUrl || '';
        const imageWrap = slide.querySelector('[data-slide-image]');
        imageWrap.hidden = imageUrl === '';
        if (imageUrl !== '') imageWrap.querySelector('img').src = imageUrl;
    };

    const updateSlideNavigator = () => {
        const items = previewSlideElements();
        previewSlideSwitcher.hidden = items.length <= 1;
        previewSlideNav.replaceChildren(...items.map((slide, index) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.dataset.previewSlide = String(index);
            button.setAttribute('aria-label', `Показать слайд ${index + 1}`);
            button.setAttribute('aria-pressed', String(slide === activeSlide));
            button.classList.toggle('is-active', slide === activeSlide);
            return button;
        }));
    };

    const renumberSlides = () => {
        const items = slideElements();
        items.forEach((slide, index) => {
            slide.querySelector('[data-slide-number]').textContent = `Слайд ${index + 1}`;
            slide.querySelector('[data-slide-summary]').textContent = field(slide, 'title').value.trim() || 'Без заголовка';
            slide.querySelectorAll('[name]').forEach((input) => {
                input.name = input.name.replace(/hero_slides\[\d+]/, `hero_slides[${index}]`);
            });
            slide.querySelector('[data-slide-up]').disabled = index === 0;
            slide.querySelector('[data-slide-down]').disabled = index === items.length - 1;
            slide.querySelector('[data-slide-remove]').disabled = items.length === 1;
        });
        addSlide.disabled = items.length >= 5;
        addSlide.textContent = items.length >= 5 ? 'Добавлено максимум 5 слайдов' : 'Добавить слайд';
        if (!activeSlide || !slides.contains(activeSlide)) activeSlide = items[0] || null;
        updateSlideNavigator();
        updateHeroPreview();
    };

    const productBlocks = () => [...productBlockEditors.querySelectorAll('[data-product-block]')];
    const productPreview = (block) => preview.querySelector(`[data-preview-product-block="${block.dataset.productBlockId}"]`);

    const updateProductBlock = (block) => {
        const title = block.querySelector('[data-product-title]').value.trim() || 'Товары';
        const columns = Number(block.querySelector('[data-product-columns]').value);
        const layout = block.querySelector('[data-product-layout]').value;
        const item = productPreview(block);
        if (!item) return;
        item.querySelector('[data-preview-product-title]').textContent = title;
        const grid = item.querySelector('[data-preview-product-grid]');
        grid.style.setProperty('--preview-columns', columns);
        grid.replaceChildren(...Array.from({ length: columns }, () => document.createElement('i')));
        grid.classList.toggle('is-carousel', layout === 'carousel');
        block.querySelector('[data-product-carousel-settings]').hidden = layout !== 'carousel';
        block.querySelector('[data-product-block-legend]').textContent = title;
        const control = sectionControl(`product:${block.dataset.productBlockId}`);
        if (control) control.querySelector('[data-section-label]').textContent = title;
    };

    const updateProductSource = (block) => {
        const source = block.querySelector('[data-product-source]').value;
        block.querySelectorAll('[data-product-source-panel]').forEach((panel) => {
            panel.hidden = panel.dataset.productSourcePanel !== source;
        });
    };

    const createProductSectionControl = (id, title) => {
        const control = document.createElement('div');
        control.className = 'section-option';
        control.dataset.sectionControl = `product:${id}`;
        control.innerHTML = `<span class="section-drag-handle" draggable="true" title="Перетащить блок" aria-label="Перетащить блок">↕</span><label><input type="checkbox" name="enabled_sections[]" value="product:${id}" checked><span data-section-label></span></label><select class="section-position" name="section_positions[product:${id}]" aria-label="Порядок товарного блока"></select><button type="button" class="icon-button" data-section-up aria-label="Переместить товарный блок выше">↑</button><button type="button" class="icon-button" data-section-down aria-label="Переместить товарный блок ниже">↓</button>`;
        control.querySelector('[data-section-label]').textContent = title;
        sectionOptions.appendChild(control);
        return control;
    };

    const renumberProductBlocks = () => {
        const blocks = productBlocks();
        blocks.forEach((block) => {
            block.querySelector('[data-product-block-remove]').disabled = blocks.length === 1;
            updateProductBlock(block);
            updateProductSource(block);
        });
        editor.querySelectorAll('[data-product-block-add]').forEach((button) => {
            button.disabled = blocks.length >= 6;
            if (button.classList.contains('product-block-add')) {
                button.textContent = blocks.length >= 6 ? 'Добавлено максимум 6 блоков' : 'Добавить товарный блок';
            }
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

    const updateGlobalStyle = () => {
        const settings = {
            font: editor.querySelector('[name="font_family"]')?.value || 'system',
            width: editor.querySelector('[name="content_width"]')?.value || 'standard',
            background: editor.querySelector('[name="page_background"]')?.value || 'white',
            corners: editor.querySelector('[name="corner_style"]')?.value || 'soft',
            spacing: editor.querySelector('[name="section_spacing"]')?.value || 'standard',
            cards: editor.querySelector('[name="card_style"]')?.value || 'outlined',
        };
        const choices = {
            font: ['system', 'modern', 'humanist', 'serif'],
            width: ['compact', 'standard', 'wide'],
            background: ['white', 'soft', 'cool'],
            corners: ['square', 'soft', 'rounded'],
            spacing: ['compact', 'standard', 'spacious'],
            cards: ['outlined', 'shadow', 'flat'],
        };
        Object.entries(settings).forEach(([group, value]) => {
            preview.classList.remove(...choices[group].map((choice) => `${group}-${choice}`));
            preview.classList.add(`${group}-${value}`);
        });
    };

    const updateLogo = () => {
        const logoUrl = editor.querySelector('[name="logo_image_id"]:checked')?.dataset.imageUrl || '';
        previewLogo.hidden = logoUrl === '';
        previewStoreName.hidden = logoUrl !== '' && !showStoreNameWithLogo.checked;
        if (logoUrl !== '') previewLogo.src = logoUrl;
    };

    const updateRichPreviews = () => {
        const value = (name) => editor.querySelector(`[name="${name}"]`)?.value.trim() || '';
        const benefits = [...editor.querySelectorAll('[name^="benefits"][name$="[title]"]')]
            .map((titleInput) => ({
                title: titleInput.value.trim(),
                text: editor.querySelector(`[name="${titleInput.name.replace('[title]', '[text]')}"]`)?.value.trim() || '',
                media: editor.querySelector(`[name="${titleInput.name.replace('[title]', '[media]')}"]`)?.value || 'system-benefit:delivery',
                color: editor.querySelector(`[name="${titleInput.name.replace('[title]', '[color]')}"]`)?.value || '#0077b6',
            }))
            .filter((benefit) => benefit.title || benefit.text)
            .slice(0, 6);
        const benefitsList = preview.querySelector('[data-preview-benefits-list]');
        preview.querySelector('[data-preview-benefits-title]').textContent = value('benefits_title') || 'Почему выбирают нас';
        benefitsList.replaceChildren(...benefits.map((benefit) => {
            const item = document.createElement('span');
            const icon = document.createElement('i');
            const image = document.createElement('img');
            const title = document.createElement('strong');
            const text = document.createElement('small');
            const info = mediaInfo(benefit.media);
            icon.className = `preview-benefit-media${info.system ? ' is-system' : ''}`;
            icon.style.setProperty('--media-url', `url("${info.url.replaceAll('"', '%22')}")`);
            icon.style.setProperty('--media-color', benefit.color);
            image.src = info.url;
            image.alt = '';
            icon.append(image);
            title.textContent = benefit.title || 'Преимущество';
            text.textContent = benefit.text;
            item.append(icon, title, text);
            return item;
        }));

        preview.querySelector('[data-preview-content-title]').textContent = value('content_title') || 'О магазине';
        preview.querySelector('[data-preview-content-text]').textContent = value('content_text') || 'Кратко расскажите о магазине.';
        const contentLayout = preview.querySelector('[data-preview-content-layout]');
        const contentImage = preview.querySelector('[data-preview-content-image]');
        const contentImageSelect = editor.querySelector('[name="content_image_id"]');
        const contentImageUrl = contentImageSelect?.selectedOptions[0]?.dataset.imageUrl || '';
        contentLayout.classList.toggle('image-left', value('content_image_position') !== 'right');
        contentLayout.classList.toggle('image-right', value('content_image_position') === 'right');
        contentImage.hidden = contentImageUrl === '';
        contentImage.style.backgroundImage = contentImageUrl === '' ? '' : `url("${contentImageUrl.replaceAll('"', '%22')}")`;

        preview.querySelector('[data-preview-reviews-title]').textContent = value('reviews_title') || 'Отзывы покупателей';
        const reviews = [...editor.querySelectorAll('[name^="reviews"][name$="[author]"]')]
            .map((authorInput) => ({
                author: authorInput.value.trim(),
                text: editor.querySelector(`[name="${authorInput.name.replace('[author]', '[text]')}"]`)?.value.trim() || '',
                media: editor.querySelector(`[name="${authorInput.name.replace('[author]', '[media]')}"]`)?.value || 'system-review:person-1',
                shape: editor.querySelector(`[name="${authorInput.name.replace('[author]', '[avatar_shape]')}"]`)?.value || 'circle',
            }))
            .filter((review) => review.author || review.text)
            .slice(0, 8);
        const reviewItems = reviews.length > 0 ? reviews : [{ author: 'Имя покупателя', text: 'Здесь появится отзыв покупателя.', media: 'system-review:person-1', shape: 'circle' }];
        preview.querySelector('[data-preview-reviews-list]').replaceChildren(...reviewItems.map((review) => {
            const item = document.createElement('article');
            const text = document.createElement('blockquote');
            const authorRow = document.createElement('div');
            const avatar = document.createElement('img');
            const author = document.createElement('strong');
            text.textContent = review.text || 'Отзыв покупателя';
            author.textContent = review.author || 'Покупатель';
            avatar.src = mediaInfo(review.media).url;
            avatar.alt = '';
            avatar.className = `shape-${review.shape}`;
            authorRow.className = 'preview-review-author';
            authorRow.append(avatar, author);
            item.append(text, authorRow);
            return item;
        }));

        preview.querySelector('[data-preview-faq-title]').textContent = value('faq_title') || 'Частые вопросы';
        const questions = [...editor.querySelectorAll('[name^="faqs"][name$="[question]"]')]
            .map((input) => input.value.trim()).filter(Boolean).slice(0, 10);
        preview.querySelector('[data-preview-faq-list]').replaceChildren(...questions.map((question) => {
            const item = document.createElement('span');
            item.textContent = question;
            return item;
        }));
    };

    const updateSections = () => {
        const controls = [...sectionOptions.querySelectorAll('[data-section-control]')];
        controls.forEach((control, index) => {
                const isHero = control.dataset.sectionControl === 'hero';
                const position = control.querySelector('.section-position');
                if (position.options.length !== controls.length) {
                    position.replaceChildren(...controls.map((item, optionIndex) => {
                        const option = document.createElement('option');
                        option.value = String(optionIndex + 1);
                        option.textContent = String(optionIndex + 1);
                        return option;
                    }));
                }
                position.value = String(index + 1);
                control.querySelector('.section-drag-handle').draggable = !isHero;
                control.querySelector('[data-section-up]').disabled = isHero || index <= 1;
                control.querySelector('[data-section-down]').disabled = isHero || index === controls.length - 1;
                const section = preview.querySelector(`[data-preview-section="${control.dataset.sectionControl}"]`);
                section.hidden = !sectionEnabled(control);
                preview.appendChild(section);
            });
        const enabledControls = controls.filter(sectionEnabled);
        enabledControls.forEach((control, index) => {
            const section = preview.querySelector(`[data-preview-section="${control.dataset.sectionControl}"]`);
            const up = section.querySelector('[data-preview-up]');
            const down = section.querySelector('[data-preview-down]');
            if (up) up.disabled = index === 1;
            if (down) down.disabled = index === enabledControls.length - 1;
        });
        const addButtons = [...blockLibrary.querySelectorAll('[data-preview-add]')];
        addButtons.forEach((button) => {
            button.hidden = sectionEnabled(sectionControl(button.dataset.previewAdd));
        });
        const hasAvailableBlocks = addButtons.some((button) => !button.hidden) || productBlocks().length < 6;
        blockLibrary.querySelector('[data-preview-library-empty]').hidden = hasAvailableBlocks;
    };

    const moveEnabledSection = (control, direction) => {
        const enabled = [...sectionOptions.querySelectorAll('[data-section-control]')].filter(sectionEnabled);
        const index = enabled.indexOf(control);
        const target = enabled[index + direction];
        if (!target || target.dataset.sectionControl === 'hero') return;
        if (direction < 0) sectionOptions.insertBefore(control, target);
        else sectionOptions.insertBefore(target, control);
        updateSections();
    };

    editor.addEventListener('input', (event) => {
        const slide = event.target.closest('[data-hero-slide]');
        if (slide) {
            activeSlide = slide;
            slide.classList.add('is-active');
            slideElements().filter((item) => item !== slide).forEach((item) => item.classList.remove('is-active'));
            slide.querySelector('[data-slide-summary]').textContent = field(slide, 'title').value.trim() || 'Без заголовка';
            updateHeroPreview();
        }
        const productBlock = event.target.closest('[data-product-block]');
        if (productBlock && event.target.matches('[data-product-title]')) updateProductBlock(productBlock);
        if (event.target.matches('[name^="faqs"][name$="[question]"], [name^="faqs"][name$="[answer]"]') && event.target.value.trim() !== '') {
            const faqControl = sectionControl('faq');
            faqControl.querySelector('input[type="checkbox"]').checked = true;
            updateSections();
        }
        if (event.target.closest('[data-config-section="benefits"], [data-config-section="content"], [data-config-section="reviews"], [data-config-section="faq"]')) updateRichPreviews();
    });

    editor.addEventListener('change', (event) => {
        const slide = event.target.closest('[data-hero-slide]');
        if (slide) {
            if (event.target.matches('[data-slide-field="image_id"]')) updateSlideImage(slide);
            updateHeroPreview();
        }
        const productBlock = event.target.closest('[data-product-block]');
        if (productBlock) {
            if (event.target.matches('[data-product-source]')) updateProductSource(productBlock);
            if (event.target.matches('[data-product-columns], [data-product-layout]')) updateProductBlock(productBlock);
        }
        if (event.target.closest('[data-config-section="benefits"]')) {
            const setting = event.target.closest('.benefit-setting');
            if (event.target.matches('[data-benefit-color]')) {
                setting.querySelector('[data-media-preview]').style.setProperty('--media-color', event.target.value);
            }
            updateRichPreviews();
        }
        if (event.target.matches('[data-review-shape]')) {
            const avatar = event.target.closest('.review-editor-meta').querySelector('[data-media-preview]');
            avatar.classList.remove('shape-circle', 'shape-rounded', 'shape-square');
            avatar.classList.add(`shape-${event.target.value}`);
        }
        if (event.target.closest('[data-config-section="content"], [data-config-section="reviews"], [data-config-section="faq"]')) updateRichPreviews();
    });

    const setPanelExpanded = (panel, expanded) => {
        const legend = panel.querySelector(':scope > legend');
        panel.classList.toggle('is-collapsed', !expanded);
        legend.setAttribute('aria-expanded', String(expanded));
    };

    const switchEditorPanel = (name) => {
        editorTabs.forEach((button) => {
            const active = button.dataset.editorTab === name;
            button.classList.toggle('is-active', active);
            button.setAttribute('aria-pressed', String(active));
        });
        editor.querySelectorAll('[data-editor-panel]').forEach((panel) => {
            panel.hidden = panel.dataset.editorPanel !== name;
        });
    };

    const initializeCollapsiblePanel = (panel, expanded) => {
        if (panel.dataset.collapsibleReady === '1') return;
        const legend = panel.querySelector(':scope > legend');
        if (!legend) return;
        panel.dataset.collapsibleReady = '1';
        panel.classList.add('is-collapsible');
        legend.setAttribute('role', 'button');
        legend.setAttribute('tabindex', '0');
        setPanelExpanded(panel, expanded);
        const toggle = () => setPanelExpanded(panel, panel.classList.contains('is-collapsed'));
        legend.addEventListener('click', toggle);
        legend.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter' && event.key !== ' ') return;
            event.preventDefault();
            toggle();
        });
    };

    const initializedPanels = new Set();
    editor.querySelectorAll('.appearance-group').forEach((panel) => {
        const panelName = panel.dataset.editorPanel || 'layout';
        initializeCollapsiblePanel(panel, !initializedPanels.has(panelName));
        initializedPanels.add(panelName);
    });

    editorTabs.forEach((button) => {
        button.addEventListener('click', () => switchEditorPanel(button.dataset.editorTab));
    });

    editor.querySelectorAll('[data-block-library-open]').forEach((button) => {
        button.addEventListener('click', () => {
            blockDialog.showModal();
        });
    });
    editor.querySelector('[data-block-library-close]').addEventListener('click', () => blockDialog.close());
    blockDialog.addEventListener('click', (event) => {
        if (event.target === blockDialog) blockDialog.close();
    });
    editor.querySelectorAll('[data-library-tab]').forEach((button) => {
        button.addEventListener('click', () => {
            editor.querySelectorAll('[data-library-tab]').forEach((item) => {
                const active = item === button;
                item.classList.toggle('is-active', active);
                item.setAttribute('aria-pressed', String(active));
            });
            editor.querySelectorAll('[data-library-panel]').forEach((panel) => {
                panel.hidden = panel.dataset.libraryPanel !== button.dataset.libraryTab;
            });
        });
    });

    editor.querySelectorAll('[data-layout-preset]').forEach((button) => {
        button.addEventListener('click', () => {
            const orderedNames = button.dataset.layoutPreset.split(',');
            const controls = [...sectionOptions.querySelectorAll('[data-section-control]')];
            controls.forEach((control) => {
                control.querySelector('input[type="checkbox"]').checked = orderedNames.includes(control.dataset.sectionControl);
            });
            orderedNames.forEach((name) => {
                const control = sectionControl(name);
                if (control) sectionOptions.appendChild(control);
            });
            controls.filter((control) => !orderedNames.includes(control.dataset.sectionControl))
                .forEach((control) => sectionOptions.appendChild(control));
            const hero = sectionControl('hero');
            if (hero) sectionOptions.prepend(hero);
            updateSections();
            blockDialog.close();
            previewStage.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });

    editor.querySelectorAll('[data-media-picker-open]').forEach((button) => {
        button.addEventListener('click', () => {
            mediaPickerTarget = button.closest('.benefit-setting, .editor-item');
            const kind = button.dataset.mediaKind;
            mediaPickerDialog.dataset.mediaKind = kind;
            mediaPickerDialog.querySelector('[data-media-picker-title]').textContent = kind === 'benefit'
                ? 'Выберите иконку или изображение'
                : 'Выберите фото для отзыва';
            mediaPickerDialog.querySelectorAll('[data-media-picker-group]').forEach((group) => {
                group.hidden = group.dataset.mediaPickerGroup !== kind && group.dataset.mediaPickerGroup !== 'uploads';
            });
            const selectedValue = mediaPickerTarget.querySelector('[data-media-value]').value;
            mediaPickerDialog.querySelectorAll('[data-media-option]').forEach((option) => {
                option.classList.toggle('is-selected', option.dataset.mediaValue === selectedValue);
            });
            mediaPickerDialog.showModal();
        });
    });
    mediaPickerDialog.querySelector('[data-media-picker-close]').addEventListener('click', () => mediaPickerDialog.close());
    mediaPickerDialog.addEventListener('click', (event) => {
        if (event.target === mediaPickerDialog) mediaPickerDialog.close();
        const option = event.target.closest('[data-media-option]');
        if (!option || !mediaPickerTarget) return;
        const input = mediaPickerTarget.querySelector('[data-media-value]');
        const imageWrap = mediaPickerTarget.querySelector('[data-media-preview]');
        const image = imageWrap.querySelector('img');
        input.value = option.dataset.mediaValue;
        image.src = option.dataset.mediaUrl;
        imageWrap.classList.toggle('is-system', option.dataset.mediaSystem === '1');
        imageWrap.style.setProperty('--media-url', `url("${option.dataset.mediaUrl.replaceAll('"', '%22')}")`);
        mediaPickerDialog.close();
        updateRichPreviews();
    });

    editor.querySelectorAll('button[data-preview-device]').forEach((button) => {
        button.addEventListener('click', () => {
            previewStage.dataset.previewDevice = button.dataset.previewDevice;
            editor.querySelectorAll('button[data-preview-device]').forEach((item) => {
                const active = item === button;
                item.classList.toggle('is-active', active);
                item.setAttribute('aria-pressed', String(active));
            });
        });
    });

    editor.addEventListener('click', (event) => {
        const addProductBlock = event.target.closest('[data-product-block-add]');
        if (addProductBlock) {
            if (productBlocks().length >= 6) return;
            const id = `products-${Date.now().toString(36)}`;
            const editorHolder = document.createElement('template');
            editorHolder.innerHTML = productBlockTemplate.innerHTML.replaceAll('__ID__', id).trim();
            const block = editorHolder.content.firstElementChild;
            const previewHolder = document.createElement('template');
            previewHolder.innerHTML = productPreviewTemplate.innerHTML.replaceAll('__ID__', id).trim();
            const previewBlock = previewHolder.content.firstElementChild;
            productBlockEditors.appendChild(block);
            preview.appendChild(previewBlock);
            createProductSectionControl(id, 'Новая подборка');
            initializeCollapsiblePanel(block, true);
            renumberProductBlocks();
            updateSections();
            if (blockDialog.open) blockDialog.close();
            block.scrollIntoView({ behavior: 'smooth', block: 'start' });
            return;
        }
        const removeProductBlock = event.target.closest('[data-product-block-remove]');
        if (removeProductBlock) {
            if (productBlocks().length <= 1) return;
            const block = removeProductBlock.closest('[data-product-block]');
            const id = block.dataset.productBlockId;
            productPreview(block)?.remove();
            sectionControl(`product:${id}`)?.remove();
            block.remove();
            renumberProductBlocks();
            updateSections();
            return;
        }
        const previewSlideButton = event.target.closest('[data-preview-slide], [data-preview-slide-prev], [data-preview-slide-next]');
        if (previewSlideButton) {
            const items = previewSlideElements();
            if (items.length <= 1) return;
            let index = items.indexOf(activeSlide);
            if (previewSlideButton.matches('[data-preview-slide]')) index = Number(previewSlideButton.dataset.previewSlide);
            if (previewSlideButton.matches('[data-preview-slide-prev]')) index = (index - 1 + items.length) % items.length;
            if (previewSlideButton.matches('[data-preview-slide-next]')) index = (index + 1) % items.length;
            selectSlide(items[index]);
            return;
        }
        const slideSelect = event.target.closest('[data-slide-select]');
        if (slideSelect) {
            const slide = slideSelect.closest('[data-hero-slide]');
            const shouldExpand = slide.querySelector('[data-slide-body]').hidden || slide !== activeSlide;
            selectSlide(slide, shouldExpand);
            if (!shouldExpand) setSlideExpanded(slide, false);
            return;
        }
        const previewAction = event.target.closest('[data-preview-up], [data-preview-down], [data-preview-edit], [data-preview-hide], [data-preview-add]');
        if (previewAction) {
            const name = previewAction.dataset.previewAdd || previewAction.closest('[data-preview-section]')?.dataset.previewSection;
            const control = sectionControl(name);
            if (previewAction.matches('[data-preview-add]')) {
                control.querySelector('input[type="checkbox"]').checked = true;
                updateSections();
                blockDialog.close();
                preview.querySelector(`[data-preview-section="${name}"]`).scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else if (previewAction.matches('[data-preview-hide]')) {
                control.querySelector('input[type="checkbox"]').checked = false;
                updateSections();
            } else if (previewAction.matches('[data-preview-up]')) {
                moveEnabledSection(control, -1);
            } else if (previewAction.matches('[data-preview-down]')) {
                moveEnabledSection(control, 1);
            } else if (previewAction.matches('[data-preview-edit]')) {
                const config = editor.querySelector(`[data-config-section="${name}"]`);
                if (config) {
                    switchEditorPanel(config.dataset.editorPanel || 'layout');
                    setPanelExpanded(config, true);
                    config.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
                else if (name === 'contacts') window.location.assign(editor.dataset.storeSettingsUrl);
            }
            return;
        }
        const sectionAction = event.target.closest('[data-section-up], [data-section-down]');
        if (sectionAction) {
            const control = sectionAction.closest('[data-section-control]');
            if (control.dataset.sectionControl === 'hero') return;
            if (sectionAction.matches('[data-section-up]') && control.previousElementSibling?.dataset.sectionControl !== 'hero') {
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
            const replacement = slide.nextElementSibling || slide.previousElementSibling;
            slide.remove();
            if (activeSlide === slide) activeSlide = replacement;
        }
        renumberSlides();
    });

    addSlide.addEventListener('click', () => {
        if (slideElements().length >= 5) return;
        const slide = slideTemplate.content.firstElementChild.cloneNode(true);
        slides.appendChild(slide);
        updateSlideImage(slide);
        selectSlide(slide, true);
        renumberSlides();
        slide.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });

    editor.querySelectorAll('[name="template"]').forEach((input) => input.addEventListener('change', updateTemplate));
    editor.querySelectorAll('[name="primary_color"]').forEach((input) => input.addEventListener('change', updateColor));
    editor.querySelectorAll('[data-global-style]').forEach((input) => input.addEventListener('change', updateGlobalStyle));
    editor.querySelectorAll('[name="logo_image_id"]').forEach((input) => input.addEventListener('change', updateLogo));
    showStoreNameWithLogo.addEventListener('change', updateLogo);
    sectionOptions.addEventListener('change', (event) => {
        if (event.target.matches('[data-section-control] input[type="checkbox"], .section-position')) updateSections();
    });
    editor.querySelectorAll('[data-category-image-select]').forEach((select) => {
        select.addEventListener('change', () => {
            const thumb = select.closest('.category-image-setting').querySelector('[data-category-image-thumb]');
            const imageUrl = select.selectedOptions[0]?.dataset.imageUrl || '';
            thumb.hidden = imageUrl === '';
            if (imageUrl !== '') thumb.querySelector('img').src = imageUrl;
        });
    });
    const contentImageSelect = editor.querySelector('[data-content-image-select]');
    contentImageSelect?.addEventListener('change', () => {
        const thumb = editor.querySelector('[data-content-image-thumb]');
        const imageUrl = contentImageSelect.selectedOptions[0]?.dataset.imageUrl || '';
        thumb.hidden = imageUrl === '';
        if (imageUrl !== '') thumb.querySelector('img').src = imageUrl;
    });

    const orderedControls = [...sectionOptions.querySelectorAll('[data-section-control]')]
        .sort((left, right) => Number(left.querySelector('.section-position').value) - Number(right.querySelector('.section-position').value));
    orderedControls.forEach((control) => sectionOptions.appendChild(control));
    const heroControl = sectionOptions.querySelector('[data-section-control="hero"]');
    if (heroControl) sectionOptions.prepend(heroControl);

    slides.addEventListener('dragstart', (event) => {
        if (!event.target.matches('.appearance-slide-handle')) return;
        const slide = event.target.closest('[data-hero-slide]');
        slide.classList.add('is-dragging');
        activeSlide = slide;
    });
    slides.addEventListener('dragover', (event) => {
        const dragging = slides.querySelector('.is-dragging');
        if (!dragging) return;
        event.preventDefault();
        const target = event.target.closest('[data-hero-slide]');
        if (!target || target === dragging) return;
        const bounds = target.getBoundingClientRect();
        slides.insertBefore(dragging, event.clientY < bounds.top + bounds.height / 2 ? target : target.nextElementSibling);
    });
    slides.addEventListener('dragend', () => {
        slides.querySelector('.is-dragging')?.classList.remove('is-dragging');
        renumberSlides();
    });

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
        if (target.dataset.sectionControl === 'hero') {
            sectionOptions.insertBefore(dragging, heroControl.nextElementSibling);
            return;
        }
        const bounds = target.getBoundingClientRect();
        sectionOptions.insertBefore(dragging, event.clientY < bounds.top + bounds.height / 2 ? target : target.nextElementSibling);
    });

    preview.addEventListener('dragstart', (event) => {
        const handle = event.target.closest('[data-preview-section] .preview-block-drag');
        if (!handle) return;
        handle.closest('[data-preview-section]').classList.add('is-preview-dragging');
    });
    preview.addEventListener('dragover', (event) => {
        const dragging = preview.querySelector('.is-preview-dragging');
        if (!dragging) return;
        event.preventDefault();
        const target = event.target.closest('[data-preview-section]');
        if (!target || target === dragging || target.dataset.previewSection === 'hero') return;
        const bounds = target.getBoundingClientRect();
        preview.insertBefore(dragging, event.clientY < bounds.top + bounds.height / 2 ? target : target.nextElementSibling);
    });
    preview.addEventListener('dragend', () => {
        preview.querySelector('.is-preview-dragging')?.classList.remove('is-preview-dragging');
        [...preview.querySelectorAll('[data-preview-section]')].forEach((section) => {
            sectionOptions.appendChild(sectionControl(section.dataset.previewSection));
        });
        sectionOptions.prepend(heroControl);
        updateSections();
    });

    slideElements().forEach((slide, index) => {
        updateSlideImage(slide);
        setSlideExpanded(slide, index === 0);
    });
    activeSlide = slideElements()[0] || null;
    renumberSlides();
    renumberProductBlocks();
    updateTemplate();
    updateColor();
    updateGlobalStyle();
    updateLogo();
    updateRichPreviews();
    updateSections();
    switchEditorPanel('layout');
})();
