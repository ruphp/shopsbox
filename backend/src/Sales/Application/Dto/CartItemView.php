<?php

declare(strict_types=1);

namespace App\Sales\Application\Dto;

final readonly class CartItemView
{
    public function __construct(
        public string $key,
        public string $productId,
        public ?string $variantId,
        public string $productName,
        public string $productSlug,
        public ?string $variantName,
        public ?string $imageUrl,
        public int $unitPriceMinor,
        public int $quantity,
        public int $lineTotalMinor,
    ) {
    }
}
