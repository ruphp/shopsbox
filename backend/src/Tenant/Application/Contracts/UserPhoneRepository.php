<?php

declare(strict_types=1);

namespace App\Tenant\Application\Contracts;

interface UserPhoneRepository
{
    public function confirmForEmail(
        string $email,
        string $phone,
        ?string $ip,
        ?string $userAgent,
        string $provider,
    ): bool;
}
