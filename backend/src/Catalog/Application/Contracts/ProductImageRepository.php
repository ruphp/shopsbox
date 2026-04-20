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

    /**
     * @return list<ProductImageView>
     */
    public function listByStoreAndProduct(string $storeId, string $productId): array;

    public function setPrimary(string $storeId, string $productId, string $imageId): ?ProductImageView;

    public function changePosition(string $storeId, string $productId, string $imageId, int $position): ?ProductImageView;

    public function delete(string $storeId, string $productId, string $imageId): bool;
}
