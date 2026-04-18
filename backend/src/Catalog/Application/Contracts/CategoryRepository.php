<?php

declare(strict_types=1);

namespace App\Catalog\Application\Contracts;

interface CategoryRepository
{
    public function existsByStore(string $storeId, string $categoryId): bool;
}
