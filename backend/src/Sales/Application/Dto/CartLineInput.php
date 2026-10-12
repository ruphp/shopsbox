<?php

declare(strict_types=1);

namespace App\Sales\Application\Dto;

final readonly class CartLineInput
{
    public function __construct(
        public string $productId,
        public ?string $variantId,
        public int $quantity,
    ) {
    }
}
