<?php

declare(strict_types=1);

namespace App\Catalog\Application\Contracts;

use App\Catalog\Application\Dto\ProductView;
use App\Catalog\Domain\ProductStatus;

interface ProductRepository
{
    /**
     * @return list<ProductView>
     */
    public function listByStore(string $storeId): array;

    public function findByStore(string $storeId, string $productId): ?ProductView;

    public function existsByStoreAndSlug(string $storeId, string $slug, ?string $exceptProductId = null): bool;

    public function persist(
        string $id,
        string $tenantId,
        string $storeId,
        ?string $categoryId,
        string $name,
        string $slug,
        ?string $description,
        ProductStatus $status,
    ): ProductView;

    public function update(
        string $storeId,
        string $productId,
        ?string $categoryId,
        string $name,
        string $slug,
        ?string $description,
    ): ?ProductView;

    public function changeStatus(string $storeId, string $productId, ProductStatus $status): ?ProductView;
}
