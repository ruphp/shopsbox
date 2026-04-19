<?php

declare(strict_types=1);

namespace App\Tenant\Application\UseCase;

use App\Tenant\Application\Contracts\StoreRepository;
use App\Tenant\Application\Dto\StoreSettingsView;
use App\Tenant\Application\Exception\StoreSettingsAccessDenied;

final readonly class ShowStoreSettingsUseCase
{
    public function __construct(private StoreRepository $storeRepository)
    {
    }

    public function execute(string $ownerEmail): StoreSettingsView
    {
        $view = $this->storeRepository->findSettingsByOwnerEmail(strtolower(trim($ownerEmail)));
        if (!$view instanceof StoreSettingsView) {
            throw new StoreSettingsAccessDenied('Store settings are not available for this user.');
        }

        return $view;
    }
}
