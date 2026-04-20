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
}
