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
    private const SECTIONS = ['hero', 'featured', 'contacts'];

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

        $template = trim($input->template);
        if (!in_array($template, self::TEMPLATES, true)) {
            throw InvalidTenantInput::forField('template', 'Unknown storefront template.');
        }

        $primaryColor = strtolower(trim($input->primaryColor));
        if (!preg_match('/^#[0-9a-f]{6}$/', $primaryColor)) {
            throw InvalidTenantInput::forField('primary_color', 'Primary color must be HEX color.');
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

        $sections = array_values(array_unique(array_filter(
            $input->sections,
            static fn (string $section): bool => in_array($section, self::SECTIONS, true),
        )));
        if ($sections === []) {
            throw InvalidTenantInput::forField('sections', 'Choose at least one storefront section.');
        }

        $images = [];
        foreach ($this->mediaLibraryRepository->listByStore($current->storeId, 'image') as $image) {
            $images[$image->id] = $image;
        }
        $logo = $this->selectedImage($input->logoImageId, $images, 'logo_image_id');
        $heroImage = $this->selectedImage($input->heroImageId, $images, 'hero_image_id');

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
            'sections' => $sections,
            'logo_image_id' => $logo?->id,
            'logo_image_url' => $logo?->publicUrl,
            'show_store_name_with_logo' => $input->showStoreNameWithLogo,
            'hero_image_id' => $heroImage?->id,
            'hero_image_url' => $heroImage?->publicUrl,
        ];

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

        return [
            'eyebrow' => $eyebrow,
            'title' => $title,
            'text' => $text,
            'button_label' => $buttonLabel,
            'button_url' => $buttonUrl,
            'secondary_label' => $secondaryLabel,
            'secondary_url' => $secondaryUrl,
            'alignment' => $alignment,
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
}
