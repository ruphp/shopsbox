<?php

declare(strict_types=1);

namespace App\Tenant\Application\Contracts;

use App\Tenant\Application\Dto\RegisterOwnerResult;

interface OwnerRegistrationRepository
{
    public function emailExists(string $email): bool;

    public function storeSlugExists(string $slug): bool;

    public function register(
        string $tenantId,
        string $storeId,
        string $userId,
        string $ownerName,
        string $email,
        string $phone,
        string $storeName,
        string $storeSlug,
        string $storeDomain,
        string $timezone,
    ): RegisterOwnerResult;
}
