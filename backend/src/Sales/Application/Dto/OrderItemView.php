<?php

declare(strict_types=1);

namespace App\Sales\Application\Dto;

final readonly class OrderItemView
{
    public function __construct(
        public string $productName,
        public ?string $variantName,
        public int $unitPriceMinor,
        public int $quantity,
        public int $lineTotalMinor,
    ) {
    }
}
