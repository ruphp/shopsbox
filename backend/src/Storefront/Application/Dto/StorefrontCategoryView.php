<?php

declare(strict_types=1);

namespace App\Storefront\Application\Dto;

final readonly class StorefrontCategoryView
{
    public function __construct(
        public string $id,
        public string $name,
        public string $slug,
    ) {
    }
}
