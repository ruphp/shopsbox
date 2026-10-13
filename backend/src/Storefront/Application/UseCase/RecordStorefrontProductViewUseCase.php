<?php

declare(strict_types=1);

namespace App\Storefront\Application\UseCase;

use App\Storefront\Application\Contracts\StorefrontProductRepository;

final readonly class RecordStorefrontProductViewUseCase
{
    public function __construct(private StorefrontProductRepository $productRepository)
    {
    }

    public function execute(string $productId): void
    {
        $this->productRepository->incrementViewCount($productId);
    }
}
