<?php

declare(strict_types=1);

namespace App\Catalog\Application\Dto;

final readonly class CategoryView
{
    public function __construct(
        public string $id,
        public string $name,
        public string $slug,
    ) {
    }
}
