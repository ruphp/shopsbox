<?php

declare(strict_types=1);

namespace App\Tenant\Application\Dto;

final readonly class UpdateStoreSettingsInput
{
    /**
     * @param array<string, mixed> $themeSettings
     */
    public function __construct(
        public string $ownerEmail,
        public string $name,
        public ?string $publicDescription,
        public ?string $contactEmail,
        public ?string $contactPhone,
        public ?string $contactCity,
        public ?string $contactAddress,
        public ?string $sellerLegalName,
        public ?string $sellerInn,
        public ?string $sellerLegalText,
        public ?string $deliveryText,
        public ?string $paymentText,
        public string $defaultCurrency,
        public string $timezone,
        public array $themeSettings = [],
    ) {
    }
}
