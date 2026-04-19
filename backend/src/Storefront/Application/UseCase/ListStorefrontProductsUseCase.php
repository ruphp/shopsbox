<?php

declare(strict_types=1);

namespace App\Storefront\Application\UseCase;

use App\Storefront\Application\Contracts\StorefrontProductRepository;
use App\Storefront\Application\Contracts\StorefrontStoreRepository;
use App\Storefront\Application\Dto\StorefrontProductListView;
use App\Storefront\Application\Exception\StorefrontStoreNotFound;

final readonly class ListStorefrontProductsUseCase
{
    public function __construct(
        private StorefrontStoreRepository $storeRepository,
        private StorefrontProductRepository $productRepository,
    ) {
    }

    public function execute(string $storeSlug): StorefrontProductListView
    {
        $store = $this->storeRepository->findActiveBySlug($storeSlug);
        if ($store === null) {
            throw StorefrontStoreNotFound::bySlug($storeSlug);
        }

        return new StorefrontProductListView(
            $store,
            $this->productRepository->listActiveByStore($store->id),
        );
    }
}
