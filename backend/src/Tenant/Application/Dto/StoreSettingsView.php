<?php

declare(strict_types=1);

namespace App\Tenant\Application\Dto;

final readonly class StoreSettingsView
{
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
    ) {
    }
}
