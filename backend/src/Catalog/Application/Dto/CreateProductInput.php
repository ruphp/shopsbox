<?php

declare(strict_types=1);

namespace App\Catalog\Application\Dto;

final readonly class CreateProductInput
{
    public function __construct(
        public string $tenantId,
        public string $storeId,
        public string $name,
        public string $slug,
        public ?string $categoryId,
        public ?string $description,
        public string $status = 'draft',
    ) {
    }
}
