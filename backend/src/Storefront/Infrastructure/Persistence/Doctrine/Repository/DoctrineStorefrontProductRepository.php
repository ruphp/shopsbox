<?php

declare(strict_types=1);

namespace App\Storefront\Infrastructure\Persistence\Doctrine\Repository;

use App\Catalog\Domain\ProductStatus;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\Product;
use App\Storefront\Application\Contracts\StorefrontProductRepository;
use App\Storefront\Application\Dto\StorefrontProductView;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineStorefrontProductRepository implements StorefrontProductRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function listActiveByStore(string $storeId): array
    {
        $products = $this->entityManager->createQueryBuilder()
            ->select('product')
            ->from(Product::class, 'product')
            ->where('IDENTITY(product.store) = :storeId')
            ->andWhere('product.status = :status')
            ->setParameter('storeId', $storeId)
            ->setParameter('status', ProductStatus::ACTIVE)
            ->orderBy('product.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return array_map(fn (Product $product): StorefrontProductView => $this->map($product), $products);
    }

    public function findActiveByStoreAndSlug(string $storeId, string $productSlug): ?StorefrontProductView
    {
        $product = $this->entityManager->createQueryBuilder()
            ->select('product')
            ->from(Product::class, 'product')
            ->where('IDENTITY(product.store) = :storeId')
            ->andWhere('product.slug = :productSlug')
            ->andWhere('product.status = :status')
            ->setParameter('storeId', $storeId)
            ->setParameter('productSlug', $productSlug)
            ->setParameter('status', ProductStatus::ACTIVE)
            ->getQuery()
            ->getOneOrNullResult();

        return $product instanceof Product ? $this->map($product) : null;
    }

    private function map(Product $product): StorefrontProductView
    {
        return new StorefrontProductView(
            $product->id(),
            $product->store()->id(),
            $product->name(),
            $product->slug(),
            $product->description(),
        );
    }
}
