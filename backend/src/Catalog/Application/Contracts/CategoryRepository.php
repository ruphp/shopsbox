<?php

declare(strict_types=1);

namespace App\Catalog\Application\Contracts;

use App\Catalog\Application\Dto\CategoryView;

interface CategoryRepository
{
    public function existsByStore(string $storeId, string $categoryId): bool;

    /**
     * @return list<CategoryView>
     */
    public function listByStore(string $storeId): array;
}
