<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Repository;

use App\Catalog\Application\Contracts\CategoryRepository;
use App\Catalog\Application\Dto\CategoryView;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 */
final class DoctrineCategoryRepository extends ServiceEntityRepository implements CategoryRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    public function existsByStore(string $storeId, string $categoryId): bool
    {
        return $this->createQueryBuilder('category')
            ->select('COUNT(category.id)')
            ->where('category.id = :categoryId')
            ->andWhere('IDENTITY(category.store) = :storeId')
            ->setParameter('categoryId', $categoryId)
            ->setParameter('storeId', $storeId)
            ->getQuery()
            ->getSingleScalarResult() > 0;
    }

    public function listByStore(string $storeId): array
    {
        $categories = $this->createQueryBuilder('category')
            ->where('IDENTITY(category.store) = :storeId')
            ->setParameter('storeId', $storeId)
            ->orderBy('category.name', 'ASC')
            ->getQuery()
            ->getResult();

        return array_map(
            static fn (Category $category): CategoryView => new CategoryView(
                $category->id(),
                $category->name(),
                $category->slug(),
            ),
            $categories,
        );
    }
}
