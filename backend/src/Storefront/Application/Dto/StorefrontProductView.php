<?php

declare(strict_types=1);

namespace App\Storefront\Application\Dto;

final readonly class StorefrontProductView
{
    public function __construct(
        public string $id,
        public string $storeId,
        public string $name,
        public string $slug,
        public ?string $description,
    ) {
    }
}
