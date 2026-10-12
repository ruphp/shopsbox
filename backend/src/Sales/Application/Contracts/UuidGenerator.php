<?php

declare(strict_types=1);

namespace App\Sales\Application\Contracts;

interface UuidGenerator
{
    public function generate(): string;
}
