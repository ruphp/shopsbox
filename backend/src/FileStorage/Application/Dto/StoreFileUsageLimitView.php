<?php

declare(strict_types=1);

namespace App\FileStorage\Application\Dto;

final readonly class StoreFileUsageLimitView
{
    public function __construct(
        public int $usedSize,
        public int $maxSize,
        public int $usedFiles,
        public int $maxFiles,
    ) {
    }

    public function sizePercent(): int
    {
        return $this->maxSize > 0 ? (int) floor(($this->usedSize / $this->maxSize) * 100) : 0;
    }

    public function filesPercent(): int
    {
        return $this->maxFiles > 0 ? (int) floor(($this->usedFiles / $this->maxFiles) * 100) : 0;
    }

    public function nearLimit(): bool
    {
        return $this->sizePercent() >= 80 || $this->filesPercent() >= 80;
    }

    public function exceeded(): bool
    {
        return $this->usedSize >= $this->maxSize || $this->usedFiles >= $this->maxFiles;
    }
}
