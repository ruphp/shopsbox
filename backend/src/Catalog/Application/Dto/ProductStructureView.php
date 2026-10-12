<?php

declare(strict_types=1);

namespace App\Catalog\Application\Dto;

final readonly class ProductStructureView
{
    /**
     * @param list<array{id: string, name: string, value: string, position: int}> $attributes
     * @param list<array{id: string, name: string, values: list<array{id: string, value: string}>}> $optionGroups
     * @param list<array{id: string, name: string, sku: string, priceAdjustment: ?string, active: bool, optionValueIds: list<string>}> $variants
     */
    public function __construct(
        public array $attributes,
        public array $optionGroups,
        public array $variants,
    ) {
    }
}
