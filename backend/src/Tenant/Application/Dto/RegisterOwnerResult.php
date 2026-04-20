<?php

declare(strict_types=1);

namespace App\Tenant\Application\Dto;

final readonly class RegisterOwnerResult
{
    public function __construct(
        public string $tenantId,
        public string $storeId,
        public string $userId,
        public string $email,
    ) {
    }
}
