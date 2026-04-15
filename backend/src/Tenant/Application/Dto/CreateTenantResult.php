<?php

declare(strict_types=1);

namespace App\Tenant\Application\Dto;

final class CreateTenantResult
{
    public function __construct(
        public readonly string $tenantId,
        public readonly string $storeId,
    ) {
    }
}
