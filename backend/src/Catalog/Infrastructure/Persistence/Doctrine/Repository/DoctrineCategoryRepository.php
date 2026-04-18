<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Repository;

use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 */
final class DoctrineCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }
}
