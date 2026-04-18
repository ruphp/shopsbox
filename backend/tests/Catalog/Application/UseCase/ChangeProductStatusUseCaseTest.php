<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Application\UseCase;

use App\Catalog\Application\Contracts\EntityFlusher;
use App\Catalog\Application\Contracts\ProductRepository;
use App\Catalog\Application\Dto\ChangeProductStatusInput;
use App\Catalog\Application\Dto\ProductView;
use App\Catalog\Application\Exception\InvalidProductStatusTransition;
use App\Catalog\Application\UseCase\ChangeProductStatusUseCase;
use App\Catalog\Domain\ProductStatus;
use PHPUnit\Framework\TestCase;

final class ChangeProductStatusUseCaseTest extends TestCase
{
    public function testItPublishesDraftProduct(): void
    {
        $repository = new FakeCatalogProductRepository([
            $this->product(status: 'draft'),
        ]);
        $flusher = new SpyCatalogEntityFlusher();
        $useCase = new ChangeProductStatusUseCase($repository, $flusher);

        $result = $useCase->execute(new ChangeProductStatusInput(
            '22222222-2222-4222-8222-222222222222',
            '33333333-3333-4333-8333-333333333333',
            'active',
        ));

        self::assertSame('active', $result->status);
        self::assertTrue($flusher->flushed);
    }

    public function testItRejectsPublishingArchivedProduct(): void
    {
        $repository = new FakeCatalogProductRepository([
            $this->product(status: 'archived'),
        ]);
        $flusher = new SpyCatalogEntityFlusher();
        $useCase = new ChangeProductStatusUseCase($repository, $flusher);

        $this->expectException(InvalidProductStatusTransition::class);

        try {
            $useCase->execute(new ChangeProductStatusInput(
                '22222222-2222-4222-8222-222222222222',
                '33333333-3333-4333-8333-333333333333',
                'active',
            ));
        } finally {
            self::assertFalse($flusher->flushed);
        }
    }

    private function product(string $status): ProductView
    {
        return new ProductView(
            '33333333-3333-4333-8333-333333333333',
            '11111111-1111-4111-8111-111111111111',
            '22222222-2222-4222-8222-222222222222',
            null,
            'Demo product',
            'demo-product',
            null,
            $status,
            '2026-04-18T13:00:00+00:00',
            '2026-04-18T13:00:00+00:00',
        );
    }
}

final class FakeCatalogProductRepository implements ProductRepository
{
    /**
     * @param list<ProductView> $products
     */
    public function __construct(private array $products)
    {
    }

    public function listByStore(string $storeId): array
    {
        return array_values(array_filter(
            $this->products,
            static fn (ProductView $product): bool => $product->storeId === $storeId,
        ));
    }

    public function findByStore(string $storeId, string $productId): ?ProductView
    {
        foreach ($this->products as $product) {
            if ($product->storeId === $storeId && $product->id === $productId) {
                return $product;
            }
        }

        return null;
    }

    public function existsByStoreAndSlug(string $storeId, string $slug, ?string $exceptProductId = null): bool
    {
        foreach ($this->products as $product) {
            if ($product->storeId === $storeId && $product->slug === $slug && $product->id !== $exceptProductId) {
                return true;
            }
        }

        return false;
    }

    public function persist(
        string $id,
        string $tenantId,
        string $storeId,
        ?string $categoryId,
        string $name,
        string $slug,
        ?string $description,
        ProductStatus $status,
    ): ProductView {
        $product = new ProductView(
            $id,
            $tenantId,
            $storeId,
            $categoryId,
            $name,
            $slug,
            $description,
            $status->value,
            '2026-04-18T13:00:00+00:00',
            '2026-04-18T13:00:00+00:00',
        );
        $this->products[] = $product;

        return $product;
    }

    public function update(
        string $storeId,
        string $productId,
        ?string $categoryId,
        string $name,
        string $slug,
        ?string $description,
    ): ?ProductView {
        $product = $this->findByStore($storeId, $productId);
        if (!$product instanceof ProductView) {
            return null;
        }

        return new ProductView(
            $product->id,
            $product->tenantId,
            $product->storeId,
            $categoryId,
            $name,
            $slug,
            $description,
            $product->status,
            $product->createdAt,
            '2026-04-18T13:01:00+00:00',
        );
    }

    public function changeStatus(string $storeId, string $productId, ProductStatus $status): ?ProductView
    {
        foreach ($this->products as $index => $product) {
            if ($product->storeId === $storeId && $product->id === $productId) {
                $changedProduct = new ProductView(
                    $product->id,
                    $product->tenantId,
                    $product->storeId,
                    $product->categoryId,
                    $product->name,
                    $product->slug,
                    $product->description,
                    $status->value,
                    $product->createdAt,
                    '2026-04-18T13:01:00+00:00',
                );
                $this->products[$index] = $changedProduct;

                return $changedProduct;
            }
        }

        return null;
    }
}

final class SpyCatalogEntityFlusher implements EntityFlusher
{
    public bool $flushed = false;

    public function flush(): void
    {
        $this->flushed = true;
    }
}
