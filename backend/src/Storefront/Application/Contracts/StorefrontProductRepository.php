<?php

declare(strict_types=1);

namespace App\Storefront\Application\Contracts;

use App\Storefront\Application\Dto\StorefrontProductView;
use App\Storefront\Application\Dto\StorefrontCategoryView;

interface StorefrontProductRepository
{
    /**
     * @return list<StorefrontCategoryView>
     */
    public function listCategoriesByStore(string $storeId): array;

    /**
     * @return list<StorefrontProductView>
     */
    public function listActiveByStore(string $storeId): array;

    /**
     * @return list<StorefrontProductView>
     */
    public function listActiveByStoreAndCategory(string $storeId, string $categorySlug): array;

    public function findCategoryByStoreAndSlug(string $storeId, string $categorySlug): ?StorefrontCategoryView;

    public function findActiveByStoreAndSlug(string $storeId, string $productSlug): ?StorefrontProductView;
}
