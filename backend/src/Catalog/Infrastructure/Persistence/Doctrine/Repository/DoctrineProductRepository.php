<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Repository;

use App\Catalog\Application\Contracts\ProductRepository;
use App\Catalog\Application\Dto\ProductView;
use App\Catalog\Domain\ProductStatus;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\Category;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\Product;
use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\Store;
use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\Tenant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use LogicException;

/**
 * @extends ServiceEntityRepository<Product>
 */
final class DoctrineProductRepository extends ServiceEntityRepository implements ProductRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);

        $this->entityManager = $this->getEntityManager();
    }

    public function listByStore(string $storeId): array
    {
        $products = $this->createQueryBuilder('product')
            ->where('IDENTITY(product.store) = :storeId')
            ->setParameter('storeId', $storeId)
            ->orderBy('product.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return array_map(fn (Product $product): ProductView => $this->map($product), $products);
    }

    public function findByStore(string $storeId, string $productId): ?ProductView
    {
        $product = $this->findEntityByStore($storeId, $productId);

        return $product instanceof Product ? $this->map($product) : null;
    }

    public function existsByStoreAndSlug(string $storeId, string $slug, ?string $exceptProductId = null): bool
    {
        $queryBuilder = $this->createQueryBuilder('product')
            ->select('COUNT(product.id)')
            ->where('IDENTITY(product.store) = :storeId')
            ->andWhere('product.slug = :slug')
            ->setParameter('storeId', $storeId)
            ->setParameter('slug', $slug);

        if ($exceptProductId !== null) {
            $queryBuilder
                ->andWhere('product.id != :exceptProductId')
                ->setParameter('exceptProductId', $exceptProductId);
        }

        return $queryBuilder->getQuery()->getSingleScalarResult() > 0;
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
        $tenant = $this->entityManager->getReference(Tenant::class, $tenantId);
        $store = $this->entityManager->getReference(Store::class, $storeId);
        $category = $categoryId !== null ? $this->entityManager->getReference(Category::class, $categoryId) : null;

        if (!$tenant instanceof Tenant || !$store instanceof Store || ($categoryId !== null && !$category instanceof Category)) {
            throw new LogicException('Catalog references must resolve to Doctrine entities.');
        }

        $product = new Product($id, $tenant, $store, $category, $name, $slug, $description, $status);
        $this->entityManager->persist($product);

        return $this->map($product);
    }

    public function update(
        string $storeId,
        string $productId,
        ?string $categoryId,
        string $name,
        string $slug,
        ?string $description,
    ): ?ProductView {
        $product = $this->findEntityByStore($storeId, $productId);
        if (!$product instanceof Product) {
            return null;
        }

        $category = $categoryId !== null ? $this->entityManager->getReference(Category::class, $categoryId) : null;
        if ($categoryId !== null && !$category instanceof Category) {
            throw new LogicException('Category reference must resolve to a Doctrine entity.');
        }

        $product->updateDetails($category, $name, $slug, $description);

        return $this->map($product);
    }

    public function changeStatus(string $storeId, string $productId, ProductStatus $status): ?ProductView
    {
        $product = $this->findEntityByStore($storeId, $productId);
        if (!$product instanceof Product) {
            return null;
        }

        $product->changeStatus($status);

        return $this->map($product);
    }

    private function findEntityByStore(string $storeId, string $productId): ?Product
    {
        return $this->createQueryBuilder('product')
            ->where('product.id = :productId')
            ->andWhere('IDENTITY(product.store) = :storeId')
            ->setParameter('productId', $productId)
            ->setParameter('storeId', $storeId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    private function map(Product $product): ProductView
    {
        return new ProductView(
            $product->id(),
            $product->tenant()->id(),
            $product->store()->id(),
            $product->category()?->id(),
            $product->name(),
            $product->slug(),
            $product->description(),
            $product->status()->value,
            $product->createdAt()->format(DATE_ATOM),
            $product->updatedAt()->format(DATE_ATOM),
        );
    }
}
