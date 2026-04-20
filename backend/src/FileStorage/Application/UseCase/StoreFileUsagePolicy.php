<?php

declare(strict_types=1);

namespace App\FileStorage\Application\UseCase;

use App\FileStorage\Application\Contracts\StoreMediaLibraryRepository;
use App\FileStorage\Application\Dto\StoreFileUsageLimitView;
use RuntimeException;

final readonly class StoreFileUsagePolicy
{
    public const MAX_SIZE = 104_857_600;
    public const MAX_FILES = 100;

    public function __construct(private StoreMediaLibraryRepository $mediaLibraryRepository)
    {
    }

    public function current(string $storeId): StoreFileUsageLimitView
    {
        return new StoreFileUsageLimitView(
            $this->mediaLibraryRepository->totalSizeByStore($storeId),
            self::MAX_SIZE,
            $this->mediaLibraryRepository->countByStore($storeId),
            self::MAX_FILES,
        );
    }

    public function assertCanAdd(string $storeId, int $size): void
    {
        $current = $this->current($storeId);
        if ($current->usedFiles + 1 > $current->maxFiles || $current->usedSize + $size > $current->maxSize) {
            throw new RuntimeException('Store file storage limit exceeded.');
        }
    }
}
