<?php

declare(strict_types=1);

namespace App\Storefront\Application\Contracts;

use App\Storefront\Application\Dto\StorefrontStoreView;

interface StorefrontStoreRepository
{
    public function findActiveBySlug(string $storeSlug): ?StorefrontStoreView;
}
