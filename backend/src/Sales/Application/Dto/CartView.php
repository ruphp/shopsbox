<?php

declare(strict_types=1);

namespace App\Sales\Application\Dto;

use App\Storefront\Application\Dto\StorefrontStoreView;

final readonly class CartView
{
    /** @param list<CartItemView> $items */
    public function __construct(
        public StorefrontStoreView $store,
        public array $items,
        public int $totalMinor,
        public string $currency,
    ) {
    }

    public function itemCount(): int
    {
        return array_sum(array_map(static fn (CartItemView $item): int => $item->quantity, $this->items));
    }
}
