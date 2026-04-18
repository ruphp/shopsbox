<?php

declare(strict_types=1);

namespace App\Catalog\Application\Dto;

final readonly class UpdateProductInput
{
    public function __construct(
        public string $storeId,
        public string $productId,
        public string $name,
        public string $slug,
        public ?string $categoryId,
        public ?string $description,
    ) {
    }
}
