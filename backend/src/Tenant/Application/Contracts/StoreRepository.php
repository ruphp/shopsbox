<?php

declare(strict_types=1);

namespace App\Tenant\Application\Contracts;

use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\Store;

interface StoreRepository
{
    public function existsByDomain(string $domain): bool;

    public function persist(Store $store): void;
}
