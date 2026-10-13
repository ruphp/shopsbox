<?php

declare(strict_types=1);

namespace App\Storefront\Application\UseCase;

use App\Storefront\Application\Contracts\StorefrontProductRepository;
use App\Storefront\Application\Contracts\StorefrontStoreRepository;
use App\Storefront\Application\Dto\StorefrontHomeView;
use App\Storefront\Application\Exception\StorefrontStoreNotFound;

final readonly class ShowStorefrontHomeUseCase
{
    public function __construct(
        private StorefrontStoreRepository $storeRepository,
        private StorefrontProductRepository $productRepository,
    ) {
    }

    public function execute(string $storeSlug): StorefrontHomeView
    {
        $store = $this->storeRepository->findActiveBySlug($storeSlug);
        if ($store === null) {
            throw StorefrontStoreNotFound::bySlug($storeSlug);
        }

        $theme = $store->themeSettings;
        $featuredLimit = max(4, min(12, (int) ($theme['featured_limit'] ?? 6)));
        $source = (string) ($theme['featured_source'] ?? 'latest');
        $products = $source === 'category' && ($theme['featured_category_slug'] ?? '') !== ''
            ? $this->productRepository->listActiveByStoreAndCategory($store->id, (string) $theme['featured_category_slug'])
            : $this->productRepository->listActiveByStore($store->id);
        if ($source === 'manual') {
            $byId = [];
            foreach ($products as $product) {
                $byId[$product->id] = $product;
            }
            $products = array_values(array_filter(array_map(
                static fn (string $id) => $byId[$id] ?? null,
                array_map('strval', (array) ($theme['featured_product_ids'] ?? [])),
            )));
        }

        return new StorefrontHomeView(
            $store,
            array_slice($products, 0, $featuredLimit),
        );
    }
}
