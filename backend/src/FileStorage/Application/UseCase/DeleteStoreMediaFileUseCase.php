<?php

declare(strict_types=1);

namespace App\FileStorage\Application\UseCase;

use App\Catalog\Application\Contracts\EntityFlusher;
use App\FileStorage\Application\Contracts\FileStorage;
use App\FileStorage\Application\Contracts\StoreMediaLibraryRepository;
use App\Tenant\Application\UseCase\ShowStoreSettingsUseCase;
use Throwable;

final readonly class DeleteStoreMediaFileUseCase
{
    public function __construct(
        private ShowStoreSettingsUseCase $showStoreSettings,
        private StoreMediaLibraryRepository $repository,
        private FileStorage $fileStorage,
        private EntityFlusher $flusher,
    ) {}

    public function execute(string $ownerEmail, string $fileId): bool
    {
        $settings = $this->showStoreSettings->execute($ownerEmail);
        $key = $this->repository->deleteIfUnused($settings->storeId, $fileId);
        if ($key === null) { return false; }
        $this->flusher->flush();
        try { $this->fileStorage->delete($key); } catch (Throwable) {}
        return true;
    }
}
