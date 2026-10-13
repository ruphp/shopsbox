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

        $themeSettings = [
            'configured' => true,
            'template' => $template,
            'primary_color' => $primaryColor,
            'accent' => $this->legacyAccent($primaryColor),
            'hero_title' => $heroTitle,
            'hero_text' => $heroText,
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
}
