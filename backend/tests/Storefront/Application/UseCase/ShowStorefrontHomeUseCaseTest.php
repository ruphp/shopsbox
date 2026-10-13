<?php

declare(strict_types=1);

namespace App\Tests\Storefront\Application\UseCase;

use App\Storefront\Application\Contracts\StorefrontProductRepository;
use App\Storefront\Application\Contracts\StorefrontStoreRepository;
use App\Storefront\Application\Dto\StorefrontCategoryView;
use App\Storefront\Application\Dto\StorefrontProductView;
use App\Storefront\Application\Dto\StorefrontStoreView;
use App\Storefront\Application\UseCase\ShowStorefrontHomeUseCase;
use PHPUnit\Framework\TestCase;

final class ShowStorefrontHomeUseCaseTest extends TestCase
{
    public function testItBuildsPreviewFromProvidedDraftTheme(): void
    {
        $stores = $this->createStub(StorefrontStoreRepository::class);
        $products = $this->createMock(StorefrontProductRepository::class);
        $products->expects(self::once())
            ->method('listPopularByStore')
            ->with('store-id')
            ->willReturn([
                $this->product('1'),
                $this->product('2'),
                $this->product('3'),
                $this->product('4'),
                $this->product('5'),
            ]);
        $products->expects(self::once())
            ->method('listCategoriesByStore')
            ->with('store-id')
            ->willReturn([new StorefrontCategoryView('category-id', 'Категория', 'category')]);
        $store = new StorefrontStoreView(
            'store-id',
            'tenant-id',
            'Магазин',
            'store',
            'store.shopsbox.ru',
            ['featured_source' => 'popular', 'featured_limit' => 4],
        );

        $view = (new ShowStorefrontHomeUseCase($stores, $products))->forStore($store);

        self::assertSame($store, $view->store);
        self::assertCount(4, $view->featuredProducts);
        self::assertSame('category', $view->categories[0]->slug);
    }

    private function product(string $id): StorefrontProductView
    {
        return new StorefrontProductView($id, 'store-id', 'Товар '.$id, 'product-'.$id, null);
    }
}
