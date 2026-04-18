<?php

declare(strict_types=1);

namespace App\Tenant\Application\Contracts;

interface TenantRepository
{
    public function persist(string $id, string $name, string $status, string $billingEmail): void;
}
