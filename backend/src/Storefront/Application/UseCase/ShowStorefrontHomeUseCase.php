<?php

declare(strict_types=1);

namespace App\Storefront\Application\UseCase;

use App\Storefront\Application\Contracts\StorefrontProductRepository;
use App\Storefront\Application\Contracts\StorefrontStoreRepository;
use App\Storefront\Application\Dto\StorefrontHomeView;
use App\Storefront\Application\Dto\StorefrontProductBlockView;
use App\Storefront\Application\Dto\StorefrontStoreView;
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

        return $this->forStore($store);
    }

    public function forStore(StorefrontStoreView $store): StorefrontHomeView
    {
        $theme = $store->themeSettings;
        $blockSettings = (array) ($theme['product_blocks'] ?? []);
        if ($blockSettings === []) {
            $blockSettings = [[
                'id' => 'featured',
                'title' => (string) ($theme['featured_title'] ?? 'Популярное'),
                'columns' => (int) ($theme['featured_columns'] ?? 4),
                'show_description' => (bool) ($theme['featured_show_description'] ?? true),
                'layout' => (string) ($theme['featured_layout'] ?? 'grid'),
                'limit' => (int) ($theme['featured_limit'] ?? 6),
                'source' => (string) ($theme['featured_source'] ?? 'latest'),
                'category_slug' => (string) ($theme['featured_category_slug'] ?? ''),
                'product_ids' => (array) ($theme['featured_product_ids'] ?? []),
                'autoplay' => (bool) ($theme['featured_autoplay'] ?? true),
                'interval' => (int) ($theme['featured_interval'] ?? 5),
            ]];
        }

        $activeProducts = null;
        $popularProducts = null;
        $categoryProducts = [];
        $productBlocks = [];
        foreach ($blockSettings as $index => $block) {
            if (!is_array($block)) {
                continue;
            }
            $source = (string) ($block['source'] ?? 'latest');
            $categorySlug = (string) ($block['category_slug'] ?? '');
            if ($source === 'popular') {
                $popularProducts ??= $this->productRepository->listPopularByStore($store->id);
                $products = $popularProducts;
            } elseif ($source === 'category' && $categorySlug !== '') {
                $categoryProducts[$categorySlug] ??= $this->productRepository->listActiveByStoreAndCategory($store->id, $categorySlug);
                $products = $categoryProducts[$categorySlug];
            } else {
                $activeProducts ??= $this->productRepository->listActiveByStore($store->id);
                $products = $activeProducts;
            }
            if ($source === 'manual') {
                $byId = [];
                foreach ($products as $product) {
                    $byId[$product->id] = $product;
                }
                $products = array_values(array_filter(array_map(
                    static fn (string $id) => $byId[$id] ?? null,
                    array_map('strval', (array) ($block['product_ids'] ?? [])),
                )));
            }
            $limit = max(4, min(12, (int) ($block['limit'] ?? 6)));
            $columns = (int) ($block['columns'] ?? 4);
            $interval = (int) ($block['interval'] ?? 5);
            $productBlocks[] = new StorefrontProductBlockView(
                (string) ($block['id'] ?? 'products-'.($index + 1)),
                (string) ($block['title'] ?? 'Товары'),
                in_array($columns, [3, 4, 5], true) ? $columns : 4,
                (bool) ($block['show_description'] ?? true),
                in_array(($block['layout'] ?? null), ['grid', 'carousel'], true) ? (string) $block['layout'] : 'carousel',
                (bool) ($block['autoplay'] ?? true),
                in_array($interval, [3, 5, 7, 10], true) ? $interval : 5,
                array_slice($products, 0, $limit),
            );
        }
        $featuredProducts = $productBlocks[0]->products ?? [];

        return new StorefrontHomeView(
            $store,
            $featuredProducts,
            $this->productRepository->listCategoriesByStore($store->id),
            $productBlocks,
        );
    }
}
