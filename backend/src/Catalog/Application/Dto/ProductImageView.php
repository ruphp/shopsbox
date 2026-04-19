<?php

declare(strict_types=1);

namespace App\Catalog\Application\Dto;

final readonly class ProductImageView
{
    public function __construct(
        public string $id,
        public string $productId,
        public string $key,
        public string $publicUrl,
        public string $mimeType,
        public int $size,
        public string $createdAt,
    ) {
    }
}
