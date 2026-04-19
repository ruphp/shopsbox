<?php

declare(strict_types=1);

namespace App\Storefront\Application\Dto;

final readonly class StorefrontProductVariantView
{
    /**
     * @param list<string> $optionValues
     */
    public function __construct(
        public string $name,
        public string $sku,
        public ?string $priceAdjustment,
        public array $optionValues,
    ) {
    }
}
