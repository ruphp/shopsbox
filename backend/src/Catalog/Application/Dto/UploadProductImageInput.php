<?php

declare(strict_types=1);

namespace App\Catalog\Application\Dto;

final readonly class UploadProductImageInput
{
    public function __construct(
        public string $storeId,
        public string $productId,
        public string $originalFilename,
        public string $mimeType,
        public int $size,
        public string $contents,
    ) {
    }
}
