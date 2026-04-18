<?php

declare(strict_types=1);

namespace App\Storefront\Application\Dto;

final readonly class StorefrontProductListView
{
    /**
     * @param list<StorefrontProductView> $products
     */
    public function __construct(
        public StorefrontStoreView $store,
        public array $products,
    ) {
    }
}
