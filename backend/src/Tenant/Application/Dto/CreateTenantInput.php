<?php

declare(strict_types=1);

namespace App\Tenant\Application\Dto;

final class CreateTenantInput
{
    public function __construct(
        public readonly string $tenantName,
        public readonly string $billingEmail,
        public readonly string $storeName,
        public readonly string $storeSlug,
        public readonly string $storeDomain,
        public readonly string $defaultCurrency,
        public readonly string $timezone,
    ) {
    }
}
