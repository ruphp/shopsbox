<?php

declare(strict_types=1);

namespace App\Storefront\Application\Dto;

final readonly class StorefrontProductBlockView
{
    /**
     * @param list<StorefrontProductView> $products
     */
    public function __construct(
        public string $id,
        public string $title,
        public int $columns,
        public bool $showDescription,
        public string $layout,
        public bool $autoplay,
        public int $interval,
        public array $products,
    ) {
    }
}
