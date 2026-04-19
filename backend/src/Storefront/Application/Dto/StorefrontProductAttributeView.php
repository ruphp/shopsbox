<?php

declare(strict_types=1);

namespace App\Storefront\Application\Dto;

final readonly class StorefrontProductAttributeView
{
    public function __construct(
        public string $name,
        public string $value,
    ) {
    }
}
