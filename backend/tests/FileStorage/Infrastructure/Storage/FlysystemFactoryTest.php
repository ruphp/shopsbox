<?php

declare(strict_types=1);

namespace App\Tests\FileStorage\Infrastructure\Storage;

use App\FileStorage\Infrastructure\Storage\FlysystemFactory;
use League\Flysystem\FilesystemOperator;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class FlysystemFactoryTest extends TestCase
{
    public function testItCreatesLocalFilesystem(): void
    {
        $root = sys_get_temp_dir() . '/shopsbox-flysystem-factory-test-' . bin2hex(random_bytes(6));
        mkdir($root, 0777, true);

        $filesystem = $this->localFactory($root)->create();

        try {
            self::assertInstanceOf(FilesystemOperator::class, $filesystem);

            $filesystem->write('demo.txt', 'demo content');

            self::assertSame('demo content', $filesystem->read('demo.txt'));
            self::assertFileExists($root . '/demo.txt');
        } finally {
            $this->removeDirectory($root);
        }
    }

    public function testItRejectsUnsupportedAdapter(): void
    {
        $factory = new FlysystemFactory(
            'unknown',
            sys_get_temp_dir(),
            'http://localhost:9000',
            'shopsbox-local',
            'shopsbox',
            'shopsbox-secret',
            'us-east-1',
            true,
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unsupported file storage adapter "unknown".');

        $factory->create();
    }

    private function localFactory(string $root): FlysystemFactory
    {
        return new FlysystemFactory(
            'local',
            $root,
            'http://localhost:9000',
            'shopsbox-local',
            'shopsbox',
            'shopsbox-secret',
            'us-east-1',
            true,
        );
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
