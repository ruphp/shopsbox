<?php

declare(strict_types=1);

namespace App\Tenant\Application\Contracts;

use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\Tenant;

interface TenantRepository
{
    public function persist(Tenant $tenant): void;
}
