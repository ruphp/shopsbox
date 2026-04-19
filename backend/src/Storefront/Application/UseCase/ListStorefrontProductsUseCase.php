<?php

declare(strict_types=1);

namespace App\Storefront\Application\UseCase;

use App\Storefront\Application\Contracts\StorefrontProductRepository;
use App\Storefront\Application\Contracts\StorefrontStoreRepository;
use App\Storefront\Application\Dto\StorefrontProductListView;
use App\Storefront\Application\Exception\StorefrontCategoryNotFound;
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
        return $this->list($storeSlug, null);
    }

    public function byCategory(string $storeSlug, string $categorySlug): StorefrontProductListView
    {
        return $this->list($storeSlug, $categorySlug);
    }

    private function list(string $storeSlug, ?string $categorySlug): StorefrontProductListView
    {
        $store = $this->storeRepository->findActiveBySlug($storeSlug);
        if ($store === null) {
            throw StorefrontStoreNotFound::bySlug($storeSlug);
        }

        $categories = $this->productRepository->listCategoriesByStore($store->id);
        $selectedCategory = $categorySlug === null
            ? null
            : $this->productRepository->findCategoryByStoreAndSlug($store->id, $categorySlug);
        if ($categorySlug !== null && $selectedCategory === null) {
            throw StorefrontCategoryNotFound::bySlug($categorySlug);
        }

        return new StorefrontProductListView(
            $store,
            $categorySlug === null
                ? $this->productRepository->listActiveByStore($store->id)
                : $this->productRepository->listActiveByStoreAndCategory($store->id, $categorySlug),
            $categories,
            $selectedCategory,
        );
    }
}
