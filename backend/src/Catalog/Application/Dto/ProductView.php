<?php

declare(strict_types=1);

namespace App\Catalog\Application\Dto;

final readonly class ProductView
{
    public function __construct(
        public string $id,
        public string $tenantId,
        public string $storeId,
        public ?string $categoryId,
        public string $name,
        public string $slug,
        public ?string $description,
        public string $status,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }
}
