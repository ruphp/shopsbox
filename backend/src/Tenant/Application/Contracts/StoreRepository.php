<?php

declare(strict_types=1);

namespace App\Tenant\Application\Contracts;

interface StoreRepository
{
    public function existsByDomain(string $domain): bool;

    public function persist(
        string $id,
        string $tenantId,
        string $name,
        string $slug,
        string $domain,
        string $status,
        string $defaultCurrency,
        string $timezone,
    ): void;
}
