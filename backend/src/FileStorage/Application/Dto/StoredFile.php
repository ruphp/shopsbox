<?php

declare(strict_types=1);

namespace App\FileStorage\Application\Dto;

final readonly class StoredFile
{
    public function __construct(
        public string $key,
        public string $publicUrl,
    ) {
    }
}
