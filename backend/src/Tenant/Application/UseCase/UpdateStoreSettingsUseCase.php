<?php

declare(strict_types=1);

namespace App\Tenant\Application\UseCase;

use App\Tenant\Application\Contracts\EntityFlusher;
use App\Tenant\Application\Contracts\StoreRepository;
use App\Tenant\Application\Dto\StoreSettingsView;
use App\Tenant\Application\Dto\UpdateStoreSettingsInput;
use App\Tenant\Application\Exception\InvalidTenantInput;
use App\Tenant\Application\Exception\StoreSettingsAccessDenied;

final readonly class UpdateStoreSettingsUseCase
{
    public function __construct(
        private StoreRepository $storeRepository,
        private EntityFlusher $entityFlusher,
    ) {
    }

    public function execute(UpdateStoreSettingsInput $input): StoreSettingsView
    {
        $name = trim($input->name);
        $description = $this->emptyToNull($input->publicDescription);
        $contactEmail = $this->emptyToNull($input->contactEmail);
        $contactPhone = $this->emptyToNull($input->contactPhone);
        $contactCity = $this->emptyToNull($input->contactCity);
        $contactAddress = $this->emptyToNull($input->contactAddress);
        $sellerLegalName = $this->emptyToNull($input->sellerLegalName);
        $sellerInn = $this->emptyToNull($input->sellerInn);
        $sellerLegalText = $this->emptyToNull($input->sellerLegalText);
        $deliveryText = $this->emptyToNull($input->deliveryText);
        $paymentText = $this->emptyToNull($input->paymentText);
        $currency = strtoupper(trim($input->defaultCurrency));
        $timezone = trim($input->timezone);
        $themeSettings = $this->normalizeThemeSettings($input->themeSettings);

        if ($name === '' || strlen($name) > 160) {
            throw InvalidTenantInput::forField('name', 'Store name must be from 1 to 160 characters.');
        }

        if ($description !== null && strlen($description) > 1000) {
            throw InvalidTenantInput::forField('public_description', 'Public description must be up to 1000 characters.');
        }

        if ($contactEmail !== null && !filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
            throw InvalidTenantInput::forField('contact_email', 'Contact email must be valid.');
        }
        if ($sellerInn !== null && !preg_match('/^(?:\d{10}|\d{12})$/', $sellerInn)) {
            throw InvalidTenantInput::forField('seller_inn', 'Seller INN must contain 10 or 12 digits.');
        }

        if (!preg_match('/^[A-Z]{3}$/', $currency)) {
            throw InvalidTenantInput::forField('default_currency', 'Default currency must be ISO 4217 code.');
        }

        if (!in_array($timezone, timezone_identifiers_list(), true)) {
            throw InvalidTenantInput::forField('timezone', 'Timezone must be valid.');
        }

        $view = $this->storeRepository->updateSettingsForOwnerEmail(
            strtolower(trim($input->ownerEmail)),
            $name,
            $description,
            $contactEmail,
            $contactPhone,
            $contactCity,
            $contactAddress,
            $sellerLegalName,
            $sellerInn,
            $sellerLegalText,
            $deliveryText,
            $paymentText,
            $currency,
            $timezone,
            $themeSettings,
        );

        if (!$view instanceof StoreSettingsView) {
            throw new StoreSettingsAccessDenied('Store settings are not available for this user.');
        }

        $this->entityFlusher->flush();

        return $view;
    }

    private function emptyToNull(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /**
     * @param array<string, mixed> $settings
     *
     * @return array<string, mixed>
     */
    private function normalizeThemeSettings(array $settings): array
    {
        $primaryColor = trim((string) ($settings['primary_color'] ?? '#0077b6'));
        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $primaryColor)) {
            throw InvalidTenantInput::forField('primary_color', 'Primary color must be HEX color.');
        }

        $heroTitle = trim((string) ($settings['hero_title'] ?? ''));
        $heroText = trim((string) ($settings['hero_text'] ?? ''));
        if (strlen($heroTitle) > 120) {
            throw InvalidTenantInput::forField('hero_title', 'Hero title must be up to 120 characters.');
        }

        if (strlen($heroText) > 500) {
            throw InvalidTenantInput::forField('hero_text', 'Hero text must be up to 500 characters.');
        }

        $sections = array_values(array_intersect(
            (array) ($settings['sections'] ?? []),
            ['hero', 'featured', 'contacts'],
        ));
        $accent = (string) ($settings['accent'] ?? 'blue');
        if (!in_array($accent, ['blue', 'green', 'coral'], true)) {
            $accent = 'blue';
        }

        return [
            'primary_color' => $primaryColor,
            'accent' => $accent,
            'hero_title' => $heroTitle,
            'hero_text' => $heroText,
            'sections' => $sections === [] ? ['hero', 'featured'] : $sections,
        ];
    }
}
