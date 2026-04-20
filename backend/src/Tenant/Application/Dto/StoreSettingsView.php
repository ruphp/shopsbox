<?php

declare(strict_types=1);

namespace App\Tenant\Application\Dto;

final readonly class StoreSettingsView
{
    /**
     * @param array<string, mixed> $themeSettings
     */
    public function __construct(
        public string $tenantId,
        public string $storeId,
        public string $name,
        public string $slug,
        public string $domain,
        public string $status,
        public string $defaultCurrency,
        public string $timezone,
        public ?string $publicDescription,
        public ?string $contactEmail,
        public ?string $contactPhone,
        public ?string $contactCity = null,
        public ?string $contactAddress = null,
        public ?string $sellerLegalName = null,
        public ?string $sellerInn = null,
        public ?string $sellerLegalText = null,
        public ?string $deliveryText = null,
        public ?string $paymentText = null,
        public array $themeSettings = [],
        public ?string $publicationOwnerName = null,
        public ?string $publicationEmail = null,
        public ?string $publicationPhone = null,
        public ?string $publicSubdomain = null,
        public string $publicationStatus = 'draft',
        public ?string $publicationTermsAcceptedAt = null,
        public ?string $publicationReviewReason = null,
    ) {
    }
}
