<?php

declare(strict_types=1);

namespace App\FileStorage\Application\Dto;

final readonly class StoreMediaLibraryView
{
    /**
     * @param list<StoreMediaFileView> $files
     */
    public function __construct(
        public array $files,
        public int $totalSize,
        public StoreFileUsageLimitView $limit,
    ) {
    }
}
