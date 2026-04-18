<?php

declare(strict_types=1);

namespace App\FileStorage\Infrastructure\Storage;

use App\FileStorage\Application\Contracts\FileStorage;
use App\FileStorage\Application\Contracts\FileUrlBuilder;
use App\FileStorage\Application\Dto\StoredFile;
use League\Flysystem\FilesystemOperator;

final class FlysystemFileStorage implements FileStorage
{
    public function __construct(
        private readonly FilesystemOperator $filesystem,
        private readonly FileUrlBuilder $urlBuilder,
    ) {
    }

    public function write(string $key, string $contents, ?string $contentType = null): StoredFile
    {
        $config = [];
        if ($contentType !== null) {
            $config['ContentType'] = $contentType;
        }

        $this->filesystem->write($key, $contents, $config);

        return new StoredFile($key, $this->urlBuilder->publicUrl($key));
    }

    public function read(string $key): string
    {
        return $this->filesystem->read($key);
    }

    public function delete(string $key): void
    {
        $this->filesystem->delete($key);
    }
}
