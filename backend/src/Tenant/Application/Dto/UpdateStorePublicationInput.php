<?php

declare(strict_types=1);

namespace App\Tenant\Application\Dto;

final readonly class UpdateStorePublicationInput
{
    public function __construct(
        public string $ownerEmail,
        public string $ownerName,
        public string $email,
        public string $phone,
        public string $publicSubdomain,
        public bool $termsAccepted,
    ) {
    }
}
