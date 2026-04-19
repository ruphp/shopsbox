<?php

declare(strict_types=1);

namespace App\Storefront\Application\Dto;

final readonly class StorefrontStoreView
{
    /**
     * @param array<string, mixed> $themeSettings
     */
    public function __construct(
        public string $id,
        public string $tenantId,
        public string $name,
        public string $slug,
        public string $domain,
        public array $themeSettings = [],
    ) {
    }
}
