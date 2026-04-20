<?php

declare(strict_types=1);

namespace App\FileStorage\Application\UseCase;

use App\FileStorage\Application\Contracts\StoreMediaLibraryRepository;
use App\FileStorage\Application\Dto\StoreMediaLibraryView;
use App\Tenant\Application\Exception\StoreSettingsAccessDenied;
use App\Tenant\Application\UseCase\ShowStoreSettingsUseCase;

final readonly class ListStoreMediaLibraryUseCase
{
    public function __construct(
        private ShowStoreSettingsUseCase $showStoreSettings,
        private StoreMediaLibraryRepository $mediaLibraryRepository,
    ) {
    }

    public function execute(string $ownerEmail, ?string $mediaType = null): StoreMediaLibraryView
    {
        $settings = $this->showStoreSettings->execute($ownerEmail);
        if ($settings->storeId === '') {
            throw new StoreSettingsAccessDenied('Store settings are not available for this user.');
        }

        $files = $this->mediaLibraryRepository->listByStore($settings->storeId, $mediaType);

        return new StoreMediaLibraryView(
            $files,
            array_sum(array_map(static fn ($file): int => $file->size, $files)),
        );
    }
}
