<?php

declare(strict_types=1);

namespace App\Catalog\Application\UseCase;

use App\Catalog\Application\Contracts\ProductRepository;
use App\Catalog\Application\Dto\ProductView;

final readonly class ListProductsUseCase
{
    public function __construct(private ProductRepository $productRepository)
    {
    }

    /**
     * @return list<ProductView>
     */
    public function execute(string $storeId): array
    {
        return $this->productRepository->listByStore($storeId);
    }
}
