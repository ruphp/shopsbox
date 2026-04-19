<?php

declare(strict_types=1);

namespace App\Storefront\Application\Dto;

final readonly class StorefrontProductListView
{
    /**
     * @param list<StorefrontProductView> $products
     * @param list<StorefrontCategoryView> $categories
     */
    public function __construct(
        public StorefrontStoreView $store,
        public array $products,
        public array $categories = [],
        public ?StorefrontCategoryView $selectedCategory = null,
    ) {
    }
}
