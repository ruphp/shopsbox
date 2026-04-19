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
        public string $defaultCurrency,
        public string $timezone,
        public array $themeSettings = [],
    ) {
    }
}
