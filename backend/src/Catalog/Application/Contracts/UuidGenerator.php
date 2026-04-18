<?php

declare(strict_types=1);

namespace App\Catalog\Application\Contracts;

interface UuidGenerator
{
    public function generate(): string;
}
