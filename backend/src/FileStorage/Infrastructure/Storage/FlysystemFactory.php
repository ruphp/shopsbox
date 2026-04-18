<?php

declare(strict_types=1);

namespace App\FileStorage\Infrastructure\Storage;

use Aws\S3\S3Client;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\Local\LocalFilesystemAdapter;
use RuntimeException;

final readonly class FlysystemFactory
{
    public function __construct(
        private string $adapter,
        private string $localRoot,
        private string $s3Endpoint,
        private string $s3Bucket,
        private string $s3AccessKey,
        private string $s3SecretKey,
        private string $s3Region,
        private bool $s3PathStyle,
    ) {
    }

    public function create(): FilesystemOperator
    {
        return match ($this->adapter) {
            'local' => new Filesystem(new LocalFilesystemAdapter($this->localRoot)),
            's3' => new Filesystem(new AwsS3V3Adapter($this->s3Client(), $this->s3Bucket)),
            default => throw new RuntimeException(sprintf('Unsupported file storage adapter "%s".', $this->adapter)),
        };
    }

    private function s3Client(): S3Client
    {
        return new S3Client([
            'version' => 'latest',
            'region' => $this->s3Region,
            'endpoint' => $this->s3Endpoint,
            'use_path_style_endpoint' => $this->s3PathStyle,
            'credentials' => [
                'key' => $this->s3AccessKey,
                'secret' => $this->s3SecretKey,
            ],
        ]);
    }
}
