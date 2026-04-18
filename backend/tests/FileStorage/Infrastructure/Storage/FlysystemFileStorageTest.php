<?php

declare(strict_types=1);

namespace App\Tests\FileStorage\Infrastructure\Storage;

use App\FileStorage\Application\Contracts\FileUrlBuilder;
use App\FileStorage\Infrastructure\Storage\FlysystemFileStorage;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use PHPUnit\Framework\TestCase;

final class FlysystemFileStorageTest extends TestCase
{
    public function testItWritesReadsAndDeletesFile(): void
    {
        $root = sys_get_temp_dir() . '/shopsbox-file-storage-test-' . bin2hex(random_bytes(6));
        mkdir($root, 0777, true);

        $storage = new FlysystemFileStorage(
            new Filesystem(new LocalFilesystemAdapter($root)),
            new StubFileUrlBuilder('http://files.local'),
        );

        try {
            $storedFile = $storage->write('tenant/demo.txt', 'demo content', 'text/plain');

            self::assertSame('tenant/demo.txt', $storedFile->key);
            self::assertSame('http://files.local/tenant/demo.txt', $storedFile->publicUrl);
            self::assertSame('demo content', $storage->read('tenant/demo.txt'));

            $storage->delete('tenant/demo.txt');

            self::assertFileDoesNotExist($root . '/tenant/demo.txt');
        } finally {
            $this->removeDirectory($root);
        }
    }

    private function removeDirectory(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        $items = scandir($path);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $child = $path . DIRECTORY_SEPARATOR . $item;
            if (is_dir($child)) {
                $this->removeDirectory($child);
                continue;
            }

            unlink($child);
        }

        rmdir($path);
    }
}

final readonly class StubFileUrlBuilder implements FileUrlBuilder
{
    public function __construct(private string $baseUrl)
    {
    }

    public function publicUrl(string $key): string
    {
        return rtrim($this->baseUrl, '/') . '/' . ltrim($key, '/');
    }
}
