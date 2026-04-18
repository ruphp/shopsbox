<?php

declare(strict_types=1);

namespace App\Catalog\Application\Dto;

final readonly class ChangeProductStatusInput
{
    public function __construct(
        public string $storeId,
        public string $productId,
        public string $status,
    ) {
    }
}
