<?php

declare(strict_types=1);

namespace App\Tenant\Infrastructure\Persistence\Doctrine\Repository;

use App\Tenant\Application\Contracts\StoreRepository;
use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\Store;
use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\Tenant;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;

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

    public function persist(
        string $id,
        string $tenantId,
        string $name,
        string $slug,
        string $domain,
        string $status,
        string $defaultCurrency,
        string $timezone,
    ): void
    {
        $tenant = $this->entityManager->getReference(Tenant::class, $tenantId);
        if (!$tenant instanceof Tenant) {
            throw new LogicException('Tenant reference must be a Tenant entity.');
        }

        $store = new Store(
            $id,
            $tenant,
            $name,
            $slug,
            $domain,
            $status,
            $defaultCurrency,
            $timezone,
        );

        $this->entityManager->persist($store);
    }
}
