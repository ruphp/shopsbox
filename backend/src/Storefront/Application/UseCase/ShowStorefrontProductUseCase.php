<?php

declare(strict_types=1);

namespace App\Storefront\Application\UseCase;

use App\Storefront\Application\Contracts\StorefrontProductRepository;
use App\Storefront\Application\Contracts\StorefrontStoreRepository;
use App\Storefront\Application\Dto\StorefrontProductPageView;
use App\Storefront\Application\Exception\StorefrontProductNotFound;
use App\Storefront\Application\Exception\StorefrontStoreNotFound;

final readonly class ShowStorefrontProductUseCase
{
    public function __construct(
        private StorefrontStoreRepository $storeRepository,
        private StorefrontProductRepository $productRepository,
    ) {
    }

    public function execute(string $storeSlug, string $productSlug): StorefrontProductPageView
    {
        $store = $this->storeRepository->findActiveBySlug($storeSlug);
        if ($store === null) {
            throw StorefrontStoreNotFound::bySlug($storeSlug);
        }

        $product = $this->productRepository->findActiveByStoreAndSlug($store->id, $productSlug);
        if ($product === null) {
            throw StorefrontProductNotFound::bySlug($productSlug);
        }

        return new StorefrontProductPageView($store, $product);
    }
}
