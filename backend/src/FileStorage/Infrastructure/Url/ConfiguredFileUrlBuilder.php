<?php

declare(strict_types=1);

namespace App\FileStorage\Infrastructure\Url;

use App\FileStorage\Application\Contracts\FileUrlBuilder;

final readonly class ConfiguredFileUrlBuilder implements FileUrlBuilder
{
    public function __construct(private string $publicBaseUrl)
    {
    }

    public function publicUrl(string $key): string
    {
        return rtrim($this->publicBaseUrl, '/') . '/' . ltrim($key, '/');
    }
}
