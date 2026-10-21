<?php

declare(strict_types=1);

namespace App\Tenant\Application\UseCase;

use App\FileStorage\Application\Contracts\StoreMediaLibraryRepository;
use App\FileStorage\Application\Dto\StoreMediaFileView;
use App\Tenant\Application\Contracts\EntityFlusher;
use App\Tenant\Application\Contracts\StoreRepository;
use App\Tenant\Application\Dto\StoreSettingsView;
use App\Tenant\Application\Dto\UpdateStoreAppearanceInput;
use App\Tenant\Application\Exception\InvalidTenantInput;
use App\Tenant\Application\Exception\StoreSettingsAccessDenied;

final readonly class UpdateStoreAppearanceUseCase
{
    private const TEMPLATES = ['classic', 'catalog', 'accent'];
    private const SECTIONS = ['hero', 'categories', 'featured', 'benefits', 'content', 'reviews', 'faq', 'contacts'];
    private const BENEFIT_ICONS = ['delivery', 'quality', 'support', 'payment', 'return', 'secure', 'gift', 'stock', 'pickup', 'custom'];
    private const REVIEW_AVATARS = ['person-1', 'person-2', 'person-3', 'person-4', 'person-5', 'person-6', 'person-7', 'person-8', 'person-9', 'person-10'];

    public function __construct(
        private StoreRepository $storeRepository,
        private EntityFlusher $entityFlusher,
        private StoreMediaLibraryRepository $mediaLibraryRepository,
    ) {
    }

    public function execute(UpdateStoreAppearanceInput $input): StoreSettingsView
    {
        $email = strtolower(trim($input->ownerEmail));
        $current = $this->storeRepository->findSettingsByOwnerEmail($email);
        if (!$current instanceof StoreSettingsView) {
            throw new StoreSettingsAccessDenied('Store settings are not available for this user.');
        }

        if (!in_array($input->action, ['draft', 'publish'], true)) {
            throw InvalidTenantInput::forField('appearance_action', 'Unknown appearance action.');
        }

        $template = trim($input->template);
        if (!in_array($template, self::TEMPLATES, true)) {
            throw InvalidTenantInput::forField('template', 'Unknown storefront template.');
        }

        $primaryColor = strtolower(trim($input->primaryColor));
        if (!preg_match('/^#[0-9a-f]{6}$/', $primaryColor)) {
            throw InvalidTenantInput::forField('primary_color', 'Primary color must be HEX color.');
        }

        $images = [];
        foreach ($this->mediaLibraryRepository->listByStore($current->storeId, 'image') as $image) {
            $images[$image->id] = $image;
        }

        $heroTitle = trim($input->heroTitle);
        $heroText = trim($input->heroText);
        if (mb_strlen($heroTitle) > 120) {
            throw InvalidTenantInput::forField('hero_title', 'Hero title must be up to 120 characters.');
        }
        if (mb_strlen($heroText) > 500) {
            throw InvalidTenantInput::forField('hero_text', 'Hero text must be up to 500 characters.');
        }

        $heroEyebrow = trim($input->heroEyebrow);
        $heroButtonLabel = trim($input->heroButtonLabel);
        $featuredTitle = trim($input->featuredTitle);
        $seoTitle = trim($input->seoTitle);
        $seoDescription = trim($input->seoDescription);
        if (mb_strlen($heroEyebrow) > 60) {
            throw InvalidTenantInput::forField('hero_eyebrow', 'Banner eyebrow must be up to 60 characters.');
        }
        if ($heroButtonLabel === '' || mb_strlen($heroButtonLabel) > 40) {
            throw InvalidTenantInput::forField('hero_button_label', 'Banner button label must contain up to 40 characters.');
        }
        if ($featuredTitle === '' || mb_strlen($featuredTitle) > 80) {
            throw InvalidTenantInput::forField('featured_title', 'Product block title must contain up to 80 characters.');
        }
        if (mb_strlen($seoTitle) > 70) {
            throw InvalidTenantInput::forField('seo_title', 'SEO title must be up to 70 characters.');
        }
        if (mb_strlen($seoDescription) > 180) {
            throw InvalidTenantInput::forField('seo_description', 'SEO description must be up to 180 characters.');
        }
        $heroAlignment = in_array($input->heroAlignment, ['left', 'center', 'right'], true)
            ? $input->heroAlignment
            : 'left';
        $heroOverlay = max(0, min(90, $input->heroOverlay));
        $featuredColumns = in_array($input->featuredColumns, [3, 4, 5], true)
            ? $input->featuredColumns
            : 4;
        $featuredLayout = in_array($input->featuredLayout, ['grid', 'carousel'], true)
            ? $input->featuredLayout
            : 'grid';
        $featuredLimit = max(4, min(12, $input->featuredLimit));
        $featuredInterval = in_array($input->featuredInterval, [3, 5, 7, 10], true)
            ? $input->featuredInterval
            : 5;
        $featuredSource = in_array($input->featuredSource, ['latest', 'popular', 'category', 'manual'], true)
            ? $input->featuredSource
            : 'latest';
        $featuredCategorySlug = mb_substr(trim($input->featuredCategorySlug), 0, 120);
        $featuredProductIds = array_values(array_unique(array_slice(array_filter(
            array_map('trim', $input->featuredProductIds),
            static fn (string $id): bool => preg_match('/^[0-9a-f-]{20,40}$/i', $id) === 1,
        ), 0, 20)));
        if ($featuredSource === 'category' && $featuredCategorySlug === '') {
            throw InvalidTenantInput::forField('featured_category_slug', 'Choose a category for the product block.');
        }
        if ($featuredSource === 'manual' && $featuredProductIds === []) {
            throw InvalidTenantInput::forField('featured_product_ids', 'Choose at least one product for the product block.');
        }

        $productBlockInputs = $input->productBlocks;
        if ($productBlockInputs === []) {
            $productBlockInputs = [[
                'id' => 'featured',
                'title' => $featuredTitle,
                'columns' => $featuredColumns,
                'show_description' => $input->featuredShowDescription,
                'layout' => $featuredLayout,
                'limit' => $featuredLimit,
                'source' => $featuredSource,
                'category_slug' => $featuredCategorySlug,
                'product_ids' => $featuredProductIds,
                'autoplay' => $input->featuredAutoplay,
                'interval' => $featuredInterval,
            ]];
        }
        $productBlocks = $this->normalizeProductBlocks($productBlockInputs);
        $firstProductBlock = $productBlocks[0];
        $featuredTitle = $firstProductBlock['title'];
        $featuredColumns = $firstProductBlock['columns'];
        $featuredLayout = $firstProductBlock['layout'];
        $featuredLimit = $firstProductBlock['limit'];
        $featuredSource = $firstProductBlock['source'];
        $featuredCategorySlug = $firstProductBlock['category_slug'];
        $featuredProductIds = $firstProductBlock['product_ids'];
        $featuredInterval = $firstProductBlock['interval'];

        $benefitsTitle = trim($input->benefitsTitle);
        if ($benefitsTitle === '' || mb_strlen($benefitsTitle) > 80) {
            throw InvalidTenantInput::forField('benefits_title', 'Benefits title must contain up to 80 characters.');
        }
        $benefits = [];
        foreach (array_slice($input->benefits, 0, 6) as $benefitIndex => $benefit) {
            $title = trim((string) ($benefit['title'] ?? ''));
            $text = trim((string) ($benefit['text'] ?? ''));
            if ($title === '' && $text === '') {
                continue;
            }
            if ($title === '' || mb_strlen($title) > 60 || mb_strlen($text) > 240) {
                throw InvalidTenantInput::forField('benefits', 'Each benefit needs a title up to 60 characters and text up to 240 characters.');
            }
            $fallbackIcon = self::BENEFIT_ICONS[$benefitIndex % count(self::BENEFIT_ICONS)];
            $legacyIcon = trim((string) ($benefit['icon'] ?? ''));
            $media = $this->normalizeMediaChoice(
                (string) ($benefit['media'] ?? ($legacyIcon === '' ? '' : 'system-benefit:'.$legacyIcon)),
                'benefit',
                $fallbackIcon,
                $images,
            );
            $color = strtolower(trim((string) ($benefit['color'] ?? $primaryColor)));
            if (!preg_match('/^#[0-9a-f]{6}$/', $color)) {
                $color = $primaryColor;
            }
            $benefits[] = [
                'title' => $title,
                'text' => $text,
                'icon' => $media['legacy_key'],
                'media_source' => $media['source'],
                'image_id' => $media['image_id'],
                'image_url' => $media['image_url'],
                'color' => $color,
            ];
        }
        $contentTitle = trim($input->contentTitle);
        $contentText = trim($input->contentText);
        $contentButtonLabel = trim($input->contentButtonLabel);
        $contentButtonUrl = $this->normalizeSlideUrl($input->contentButtonUrl, 'content_button_url');
        if ($contentTitle === '' || mb_strlen($contentTitle) > 120) {
            throw InvalidTenantInput::forField('content_title', 'Content title must contain up to 120 characters.');
        }
        if (mb_strlen($contentText) > 1500) {
            throw InvalidTenantInput::forField('content_text', 'Content text must be up to 1500 characters.');
        }
        if (mb_strlen($contentButtonLabel) > 40) {
            throw InvalidTenantInput::forField('content_button_label', 'Content button label must be up to 40 characters.');
        }
        if ($contentButtonLabel !== '' && $contentButtonUrl === '') {
            throw InvalidTenantInput::forField('content_button_url', 'Choose a link for the content button.');
        }
        $contentImagePosition = in_array($input->contentImagePosition, ['left', 'right'], true)
            ? $input->contentImagePosition
            : 'left';
        $reviewsTitle = $this->requiredHeading($input->reviewsTitle, 'reviews_title');
        $reviews = [];
        foreach (array_slice($input->reviews, 0, 8) as $reviewIndex => $review) {
            $author = trim((string) ($review['author'] ?? ''));
            $text = trim((string) ($review['text'] ?? ''));
            if ($author === '' && $text === '') {
                continue;
            }
            if ($author === '' || mb_strlen($author) > 80 || $text === '' || mb_strlen($text) > 600) {
                throw InvalidTenantInput::forField('reviews', 'Each review needs an author up to 80 characters and text up to 600 characters.');
            }
            $fallbackAvatar = self::REVIEW_AVATARS[$reviewIndex % count(self::REVIEW_AVATARS)];
            $legacyAvatar = trim((string) ($review['avatar'] ?? ''));
            $media = $this->normalizeMediaChoice(
                (string) ($review['media'] ?? ($legacyAvatar === '' ? '' : 'system-review:'.$legacyAvatar)),
                'review',
                $fallbackAvatar,
                $images,
            );
            $avatarShape = in_array(($review['avatar_shape'] ?? null), ['circle', 'rounded', 'square'], true)
                ? (string) $review['avatar_shape']
                : 'circle';
            $reviews[] = [
                'author' => $author,
                'text' => $text,
                'rating' => max(1, min(5, (int) ($review['rating'] ?? 5))),
                'avatar' => $media['legacy_key'],
                'media_source' => $media['source'],
                'avatar_image_id' => $media['image_id'],
                'avatar_url' => $media['image_url'],
                'avatar_shape' => $avatarShape,
            ];
        }
        $faqTitle = $this->requiredHeading($input->faqTitle, 'faq_title');
        $faqs = [];
        foreach (array_slice($input->faqs, 0, 10) as $faq) {
            $question = trim((string) ($faq['question'] ?? ''));
            $answer = trim((string) ($faq['answer'] ?? ''));
            if ($question === '' && $answer === '') {
                continue;
            }
            if ($question === '' || mb_strlen($question) > 180 || $answer === '' || mb_strlen($answer) > 1000) {
                throw InvalidTenantInput::forField('faqs', 'Each FAQ item needs a question up to 180 characters and an answer up to 1000 characters.');
            }
            $faqs[] = ['question' => $question, 'answer' => $answer];
        }

        $productBlockIds = array_column($productBlocks, 'id');
        $sections = array_map(
            static fn (string $section): string => $section === 'featured' ? 'product:'.$productBlockIds[0] : $section,
            $input->sections,
        );
        $sections = array_values(array_unique(array_filter(
            $sections,
            static fn (string $section): bool => in_array($section, self::SECTIONS, true)
                || (str_starts_with($section, 'product:') && in_array(substr($section, 8), $productBlockIds, true)),
        )));
        if ($sections === []) {
            throw InvalidTenantInput::forField('sections', 'Choose at least one storefront section.');
        }
        $sections = array_values(array_filter($sections, static fn (string $section): bool => $section !== 'hero'));
        array_unshift($sections, 'hero');

        $logo = $this->selectedImage($input->logoImageId, $images, 'logo_image_id');
        $heroImage = $this->selectedImage($input->heroImageId, $images, 'hero_image_id');
        $contentImage = $this->selectedImage($input->contentImageId, $images, 'content_image_id');
        $categoryImages = [];
        foreach ($input->categoryImageIds as $categorySlug => $imageId) {
            $categorySlug = trim((string) $categorySlug);
            $imageId = trim((string) $imageId);
            if ($imageId === '') {
                continue;
            }
            if (!preg_match('/^[a-z0-9][a-z0-9-]{0,119}$/', $categorySlug)) {
                throw InvalidTenantInput::forField('category_image_ids', 'Unknown category in image settings.');
            }
            $image = $this->selectedImage($imageId, $images, 'category_image_ids');
            $categoryImages[$categorySlug] = [
                'image_id' => $image?->id,
                'image_url' => $image?->publicUrl,
            ];
        }

        $heroSlides = [];
        if ($input->heroSlides !== []) {
            if (count($input->heroSlides) > 5) {
                throw InvalidTenantInput::forField('hero_slides', 'A banner can contain up to five slides.');
            }
            foreach ($input->heroSlides as $index => $slide) {
                $heroSlides[] = $this->normalizeSlide($slide, $images, $index);
            }
            $firstSlide = $heroSlides[0];
            $heroTitle = $firstSlide['title'];
            $heroText = $firstSlide['text'];
            $heroEyebrow = $firstSlide['eyebrow'];
            $heroButtonLabel = $firstSlide['button_label'];
            $heroAlignment = $firstSlide['alignment'];
            $heroOverlay = $firstSlide['overlay'];
            $heroImage = $firstSlide['image_id'] === null ? null : $images[$firstSlide['image_id']];
        }
        $heroInterval = in_array($input->heroInterval, [3, 5, 7, 10], true) ? $input->heroInterval : 5;

        $themeSettings = [
            'configured' => true,
            'template' => $template,
            'primary_color' => $primaryColor,
            'accent' => $this->legacyAccent($primaryColor),
            'hero_title' => $heroTitle,
            'hero_text' => $heroText,
            'hero_eyebrow' => $heroEyebrow,
            'hero_button_label' => $heroButtonLabel,
            'hero_alignment' => $heroAlignment,
            'hero_overlay' => $heroOverlay,
            'featured_title' => $featuredTitle,
            'featured_columns' => $featuredColumns,
            'featured_show_description' => $input->featuredShowDescription,
            'seo_title' => $seoTitle,
            'seo_description' => $seoDescription,
            'hero_slides' => $heroSlides,
            'hero_autoplay' => $input->heroAutoplay,
            'hero_interval' => $heroInterval,
            'featured_layout' => $featuredLayout,
            'featured_limit' => $featuredLimit,
            'featured_source' => $featuredSource,
            'featured_category_slug' => $featuredCategorySlug,
            'featured_product_ids' => $featuredProductIds,
            'featured_autoplay' => $firstProductBlock['autoplay'],
            'featured_interval' => $featuredInterval,
            'product_blocks' => $productBlocks,
            'category_images' => $categoryImages,
            'benefits_title' => $benefitsTitle,
            'benefits' => $benefits,
            'content_title' => $contentTitle,
            'content_text' => $contentText,
            'content_image_id' => $contentImage?->id,
            'content_image_url' => $contentImage?->publicUrl,
            'content_image_position' => $contentImagePosition,
            'content_button_label' => $contentButtonLabel,
            'content_button_url' => $contentButtonUrl,
            'reviews_title' => $reviewsTitle,
            'reviews' => $reviews,
            'faq_title' => $faqTitle,
            'faqs' => $faqs,
            'sections' => $sections,
            'logo_image_id' => $logo?->id,
            'logo_image_url' => $logo?->publicUrl,
            'show_store_name_with_logo' => $input->showStoreNameWithLogo,
            'hero_image_id' => $heroImage?->id,
            'hero_image_url' => $heroImage?->publicUrl,
        ];

        if ($input->action === 'draft') {
            $publishedTheme = $current->themeSettings;
            unset($publishedTheme['appearance_draft']);
            $publishedTheme['appearance_draft'] = $themeSettings;
            $themeSettings = $publishedTheme;
        }

        $view = $this->storeRepository->updateSettingsForOwnerEmail(
            $email,
            $current->name,
            $current->publicDescription,
            $current->contactEmail,
            $current->contactPhone,
            $current->contactCity,
            $current->contactAddress,
            $current->sellerLegalName,
            $current->sellerInn,
            $current->sellerLegalText,
            $current->deliveryText,
            $current->paymentText,
            $current->defaultCurrency,
            $current->timezone,
            $themeSettings,
        );

        if (!$view instanceof StoreSettingsView) {
            throw new StoreSettingsAccessDenied('Store settings are not available for this user.');
        }

        $this->entityFlusher->flush();

        return $view;
    }

    private function legacyAccent(string $color): string
    {
        return match ($color) {
            '#16876b' => 'green',
            '#ff5a3c' => 'coral',
            default => 'blue',
        };
    }

    /**
     * @param list<array<string, mixed>> $blocks
     *
     * @return list<array<string, mixed>>
     */
    private function normalizeProductBlocks(array $blocks): array
    {
        if (count($blocks) > 6) {
            throw InvalidTenantInput::forField('product_blocks', 'The homepage can contain up to six product blocks.');
        }

        $normalized = [];
        $usedIds = [];
        foreach ($blocks as $index => $block) {
            $id = strtolower(trim((string) ($block['id'] ?? '')));
            if (!preg_match('/^[a-z0-9][a-z0-9-]{0,39}$/', $id) || isset($usedIds[$id])) {
                $id = 'products-'.($index + 1);
            }
            while (isset($usedIds[$id])) {
                $id = 'products-'.($index + 1).'-'.count($usedIds);
            }
            $usedIds[$id] = true;

            $title = trim((string) ($block['title'] ?? ''));
            if ($title === '' || mb_strlen($title) > 80) {
                throw InvalidTenantInput::forField('product_blocks', 'Each product block needs a title up to 80 characters.');
            }
            $source = in_array(($block['source'] ?? null), ['latest', 'popular', 'category', 'manual'], true)
                ? (string) $block['source']
                : 'latest';
            $categorySlug = mb_substr(trim((string) ($block['category_slug'] ?? '')), 0, 120);
            $productIds = array_values(array_unique(array_slice(array_filter(
                array_map(static fn (mixed $productId): string => trim((string) $productId), (array) ($block['product_ids'] ?? [])),
                static fn (string $productId): bool => preg_match('/^[0-9a-f-]{20,40}$/i', $productId) === 1,
            ), 0, 20)));
            if ($source === 'category' && $categorySlug === '') {
                throw InvalidTenantInput::forField('product_blocks', 'Choose a category for each category product block.');
            }
            if ($source === 'manual' && $productIds === []) {
                throw InvalidTenantInput::forField('product_blocks', 'Choose at least one product for each manual product block.');
            }

            $normalized[] = [
                'id' => $id,
                'title' => $title,
                'columns' => in_array((int) ($block['columns'] ?? 4), [3, 4, 5], true) ? (int) $block['columns'] : 4,
                'show_description' => filter_var($block['show_description'] ?? false, FILTER_VALIDATE_BOOL),
                'layout' => in_array(($block['layout'] ?? null), ['grid', 'carousel'], true) ? (string) $block['layout'] : 'carousel',
                'limit' => max(4, min(12, (int) ($block['limit'] ?? 6))),
                'source' => $source,
                'category_slug' => $categorySlug,
                'product_ids' => $productIds,
                'autoplay' => filter_var($block['autoplay'] ?? false, FILTER_VALIDATE_BOOL),
                'interval' => in_array((int) ($block['interval'] ?? 5), [3, 5, 7, 10], true) ? (int) $block['interval'] : 5,
            ];
        }

        if ($normalized === []) {
            throw InvalidTenantInput::forField('product_blocks', 'Add at least one product block.');
        }

        return $normalized;
    }

    /**
     * @param array<string, StoreMediaFileView> $images
     */
    private function selectedImage(?string $imageId, array $images, string $field): ?StoreMediaFileView
    {
        if ($imageId === null) {
            return null;
        }
        if (!isset($images[$imageId])) {
            throw InvalidTenantInput::forField($field, 'Choose an image from the store media library.');
        }

        return $images[$imageId];
    }

    /**
     * @param array<string, StoreMediaFileView> $images
     *
     * @return array{source: string, image_id: ?string, image_url: string, legacy_key: string}
     */
    private function normalizeMediaChoice(string $choice, string $kind, string $fallbackKey, array $images): array
    {
        $choice = trim($choice);
        if (str_starts_with($choice, 'media:')) {
            $image = $this->selectedImage(substr($choice, 6) ?: null, $images, $kind === 'benefit' ? 'benefits' : 'reviews');

            return [
                'source' => 'media:'.$image->id,
                'image_id' => $image->id,
                'image_url' => $image->publicUrl,
                'legacy_key' => $fallbackKey,
            ];
        }

        $prefix = $kind === 'benefit' ? 'system-benefit:' : 'system-review:';
        $allowed = $kind === 'benefit' ? self::BENEFIT_ICONS : self::REVIEW_AVATARS;
        $key = str_starts_with($choice, $prefix) ? substr($choice, strlen($prefix)) : '';
        if (!in_array($key, $allowed, true)) {
            $key = $fallbackKey;
        }
        $folder = $kind === 'benefit' ? 'benefits' : 'people';
        $extension = $kind === 'benefit' ? 'svg' : 'jpg';

        return [
            'source' => $prefix.$key,
            'image_id' => null,
            'image_url' => sprintf('/assets/system-media/%s/%s.%s', $folder, $key, $extension),
            'legacy_key' => $key,
        ];
    }

    /**
     * @param array<string, mixed> $slide
     * @param array<string, StoreMediaFileView> $images
     *
     * @return array<string, mixed>
     */
    private function normalizeSlide(array $slide, array $images, int $index): array
    {
        $number = $index + 1;
        $title = trim((string) ($slide['title'] ?? ''));
        $eyebrow = trim((string) ($slide['eyebrow'] ?? ''));
        $text = trim((string) ($slide['text'] ?? ''));
        $buttonLabel = trim((string) ($slide['button_label'] ?? ''));
        $buttonUrl = $this->normalizeSlideUrl((string) ($slide['button_url'] ?? ''), 'hero_slides');
        $secondaryLabel = trim((string) ($slide['secondary_label'] ?? ''));
        $secondaryUrl = $this->normalizeSlideUrl((string) ($slide['secondary_url'] ?? ''), 'hero_slides');
        if ($title === '' || mb_strlen($title) > 120) {
            throw InvalidTenantInput::forField('hero_slides', sprintf('Slide %d title must contain up to 120 characters.', $number));
        }
        if (mb_strlen($eyebrow) > 60 || mb_strlen($text) > 500) {
            throw InvalidTenantInput::forField('hero_slides', sprintf('Slide %d text is too long.', $number));
        }
        if (mb_strlen($buttonLabel) > 40 || mb_strlen($secondaryLabel) > 40) {
            throw InvalidTenantInput::forField('hero_slides', sprintf('Slide %d button label is too long.', $number));
        }
        if ($secondaryLabel !== '' && $secondaryUrl === '') {
            throw InvalidTenantInput::forField('hero_slides', sprintf('Choose a link for the second button on slide %d.', $number));
        }

        $imageId = trim((string) ($slide['image_id'] ?? '')) ?: null;
        $image = $this->selectedImage($imageId, $images, 'hero_slides');
        $alignment = in_array(($slide['alignment'] ?? null), ['left', 'center', 'right'], true)
            ? (string) $slide['alignment']
            : 'left';
        $verticalAlignment = in_array(($slide['vertical_alignment'] ?? null), ['top', 'center', 'bottom'], true)
            ? (string) $slide['vertical_alignment']
            : 'center';
        $contentWidth = in_array(($slide['content_width'] ?? null), ['narrow', 'medium', 'wide'], true)
            ? (string) $slide['content_width']
            : 'medium';
        $imagePosition = in_array(($slide['image_position'] ?? null), ['left', 'center', 'right'], true)
            ? (string) $slide['image_position']
            : 'center';
        $height = in_array(($slide['height'] ?? null), ['compact', 'regular', 'tall'], true)
            ? (string) $slide['height']
            : 'regular';
        $textTone = in_array(($slide['text_tone'] ?? null), ['auto', 'light', 'dark'], true)
            ? (string) $slide['text_tone']
            : 'auto';
        $buttonStyle = in_array(($slide['button_style'] ?? null), ['accent', 'light', 'outline'], true)
            ? (string) $slide['button_style']
            : 'accent';
        $contentOrder = in_array(($slide['content_order'] ?? null), ['text-first', 'buttons-first'], true)
            ? (string) $slide['content_order']
            : 'text-first';

        return [
            'eyebrow' => $eyebrow,
            'title' => $title,
            'text' => $text,
            'button_label' => $buttonLabel,
            'button_url' => $buttonUrl,
            'secondary_label' => $secondaryLabel,
            'secondary_url' => $secondaryUrl,
            'alignment' => $alignment,
            'vertical_alignment' => $verticalAlignment,
            'content_width' => $contentWidth,
            'image_position' => $imagePosition,
            'height' => $height,
            'text_tone' => $textTone,
            'button_style' => $buttonStyle,
            'content_order' => $contentOrder,
            'overlay' => max(0, min(90, (int) ($slide['overlay'] ?? 60))),
            'image_id' => $image?->id,
            'image_url' => $image?->publicUrl,
        ];
    }

    private function normalizeSlideUrl(string $url, string $field): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }
        if (!preg_match('~^(?:https?://|/[^/]|\#)~i', $url)) {
            throw InvalidTenantInput::forField($field, 'Banner links must use https://, http://, /path or #anchor.');
        }

        return mb_substr($url, 0, 500);
    }

    private function requiredHeading(string $heading, string $field): string
    {
        $heading = trim($heading);
        if ($heading === '' || mb_strlen($heading) > 80) {
            throw InvalidTenantInput::forField($field, 'Section title must contain up to 80 characters.');
        }

        return $heading;
    }
}
