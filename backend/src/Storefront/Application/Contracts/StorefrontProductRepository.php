<?php

declare(strict_types=1);

namespace App\Storefront\Application\Contracts;

use App\Storefront\Application\Dto\StorefrontProductView;

interface StorefrontProductRepository
{
    /**
     * @return list<StorefrontProductView>
     */
    public function listActiveByStore(string $storeId): array;

    public function findActiveByStoreAndSlug(string $storeId, string $productSlug): ?StorefrontProductView;
}
