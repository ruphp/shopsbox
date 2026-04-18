<?php

declare(strict_types=1);

namespace App\FileStorage\Application\Contracts;

interface FileUrlBuilder
{
    public function publicUrl(string $key): string;
}
