<?php

declare(strict_types=1);

namespace App\Catalog\Application\Contracts;

use App\Catalog\Application\Dto\ProductImageView;

interface ProductImageRepository
{
    public function persist(
        string $id,
        string $productId,
        string $key,
        string $publicUrl,
        string $mimeType,
        int $size,
    ): ProductImageView;
}
