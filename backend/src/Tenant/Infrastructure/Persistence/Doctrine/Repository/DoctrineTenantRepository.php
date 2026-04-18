<?php

declare(strict_types=1);

namespace App\Tenant\Infrastructure\Persistence\Doctrine\Repository;

use App\Tenant\Application\Contracts\TenantRepository;
use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\Tenant;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineTenantRepository implements TenantRepository
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function persist(string $id, string $name, string $status, string $billingEmail): void
    {
        $this->entityManager->persist(new Tenant($id, $name, $status, $billingEmail));
    }
}
