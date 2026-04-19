<?php

declare(strict_types=1);

namespace App\Tenant\Application\Contracts;

use App\Tenant\Application\Dto\StoreSettingsView;

interface StoreRepository
{
    public function existsByDomain(string $domain): bool;

    public function findSettingsByOwnerEmail(string $email): ?StoreSettingsView;

    public function updateSettingsForOwnerEmail(
        string $email,
        string $name,
        ?string $publicDescription,
        ?string $contactEmail,
        ?string $contactPhone,
        string $defaultCurrency,
        string $timezone,
        array $themeSettings,
    ): ?StoreSettingsView;

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
