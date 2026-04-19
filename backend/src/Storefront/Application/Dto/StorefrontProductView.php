<?php

declare(strict_types=1);

namespace App\Storefront\Application\Dto;

final readonly class StorefrontProductView
{
    /**
     * @param list<StorefrontProductAttributeView> $attributes
     * @param list<StorefrontProductOptionGroupView> $optionGroups
     * @param list<StorefrontProductVariantView> $variants
     */
    public function __construct(
        public string $id,
        public string $storeId,
        public string $name,
        public string $slug,
        public ?string $description,
        public ?StorefrontCategoryView $category = null,
        public ?string $imageUrl = null,
        public array $attributes = [],
        public array $optionGroups = [],
        public array $variants = [],
    ) {
    }
}
