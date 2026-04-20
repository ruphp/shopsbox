<?php

declare(strict_types=1);

namespace App\Tenant\Application\Dto;

final readonly class RegisterOwnerInput
{
    public function __construct(
        public string $ownerName,
        public string $email,
        public string $phone,
        public string $storeName,
        public string $storeSlug,
        public string $timezone = 'Asia/Yekaterinburg',
    ) {
    }
}
