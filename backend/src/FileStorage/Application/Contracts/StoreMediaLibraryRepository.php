<?php

declare(strict_types=1);

namespace App\FileStorage\Application\Contracts;

use App\FileStorage\Application\Dto\StoreMediaFileView;

interface StoreMediaLibraryRepository
{
    /**
     * @return list<StoreMediaFileView>
     */
    public function listByStore(string $storeId, ?string $mediaType = null): array;

    public function totalSizeByStore(string $storeId): int;

    public function countByStore(string $storeId): int;

    public function persist(string $id, string $storeId, string $key, string $publicUrl, string $originalName, string $mimeType, int $size): StoreMediaFileView;

    public function findByStore(string $storeId, string $fileId): ?StoreMediaFileView;

    public function deleteIfUnused(string $storeId, string $fileId): ?string;
}
