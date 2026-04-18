<?php

declare(strict_types=1);

namespace App\FileStorage\Application\Contracts;

use App\FileStorage\Application\Dto\StoredFile;

interface FileStorage
{
    public function write(string $key, string $contents, ?string $contentType = null): StoredFile;

    public function read(string $key): string;

    public function delete(string $key): void;
}
