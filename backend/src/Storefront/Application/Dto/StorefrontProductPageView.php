<?php

declare(strict_types=1);

namespace App\Storefront\Application\Dto;

final readonly class StorefrontProductPageView
{
    public function __construct(
        public StorefrontStoreView $store,
        public StorefrontProductView $product,
    ) {
    }
}
