<?php

declare(strict_types=1);

namespace App\Storefront\Application\Dto;

final readonly class StorefrontProductOptionGroupView
{
    /**
     * @param list<StorefrontProductOptionValueView> $values
     */
    public function __construct(
        public string $name,
        public array $values,
    ) {
    }
}
