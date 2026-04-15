<?php

declare(strict_types=1);

namespace App\Tenant\Infrastructure\Persistence\Doctrine\Repository;

use App\Tenant\Application\Contracts\StoreRepository;
use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\Store;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineStoreRepository implements StoreRepository
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function existsByDomain(string $domain): bool
    {
        return $this->entityManager->createQueryBuilder()
            ->select('COUNT(store.id)')
            ->from(Store::class, 'store')
            ->where('store.domain = :domain')
            ->setParameter('domain', $domain)
            ->getQuery()
            ->getSingleScalarResult() > 0;
    }

    public function persist(Store $store): void
    {
        $this->entityManager->persist($store);
    }
}
