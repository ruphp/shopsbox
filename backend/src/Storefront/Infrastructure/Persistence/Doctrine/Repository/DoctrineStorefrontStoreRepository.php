<?php

declare(strict_types=1);

namespace App\Storefront\Infrastructure\Persistence\Doctrine\Repository;

use App\Storefront\Application\Contracts\StorefrontStoreRepository;
use App\Storefront\Application\Dto\StorefrontStoreView;
use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\Store;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineStorefrontStoreRepository implements StorefrontStoreRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function findActiveBySlug(string $storeSlug): ?StorefrontStoreView
    {
        $store = $this->entityManager->createQueryBuilder()
            ->select('store')
            ->from(Store::class, 'store')
            ->where('store.slug = :storeSlug')
            ->andWhere('store.status = :status')
            ->setParameter('storeSlug', $storeSlug)
            ->setParameter('status', 'active')
            ->getQuery()
            ->getOneOrNullResult();

        if (!$store instanceof Store) {
            return null;
        }

        return new StorefrontStoreView(
            $store->id(),
            $store->tenant()->id(),
            $store->name(),
            $store->slug(),
            $store->domain(),
        );
    }
}
