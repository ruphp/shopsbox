<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Adapters;

use App\Catalog\Application\Contracts\UuidGenerator;
use Symfony\Component\Uid\Uuid;

final class SymfonyUuidGenerator implements UuidGenerator
{
    public function generate(): string
    {
        return Uuid::v7()->toRfc4122();
    }
}
