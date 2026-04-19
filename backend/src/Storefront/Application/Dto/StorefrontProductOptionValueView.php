<?php

declare(strict_types=1);

namespace App\Storefront\Application\Dto;

final readonly class StorefrontProductOptionValueView
{
    public function __construct(
        public string $code,
        public string $value,
    ) {
    }
}
