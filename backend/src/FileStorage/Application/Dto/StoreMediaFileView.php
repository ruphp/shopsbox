<?php

declare(strict_types=1);

namespace App\FileStorage\Application\Dto;

final readonly class StoreMediaFileView
{
    public function __construct(
        public string $id,
        public string $key,
        public string $publicUrl,
        public string $mimeType,
        public string $mediaType,
        public int $size,
        public string $usage,
        public string $createdAt,
    ) {
    }
}
