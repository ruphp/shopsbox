<?php

declare(strict_types=1);

namespace App\Storefront\Application\Dto;

final readonly class StorefrontHomeView
{
    /** @var array<string, StorefrontProductBlockView> */
    public array $productBlocksById;

    /**
     * @param list<StorefrontProductView> $featuredProducts
     * @param list<StorefrontCategoryView> $categories
     * @param list<StorefrontProductBlockView> $productBlocks
     */
    public function __construct(
        public StorefrontStoreView $store,
        public array $featuredProducts,
        public array $categories = [],
        public array $productBlocks = [],
    ) {
        $indexed = [];
        foreach ($productBlocks as $block) {
            $indexed[$block->id] = $block;
        }
        $this->productBlocksById = $indexed;
    }
}
