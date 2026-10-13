<?php

declare(strict_types=1);

namespace App\Storefront\Application\Dto;

final readonly class StorefrontHomeView
{
    /**
     * @param list<StorefrontProductView> $featuredProducts
     * @param list<StorefrontCategoryView> $categories
     */
    public function __construct(
        public StorefrontStoreView $store,
        public array $featuredProducts,
        public array $categories = [],
    ) {
    }
}
