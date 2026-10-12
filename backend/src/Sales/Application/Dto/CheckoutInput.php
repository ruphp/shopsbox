<?php

declare(strict_types=1);

namespace App\Sales\Application\Dto;

final readonly class CheckoutInput
{
    /** @param list<CartLineInput> $lines */
    public function __construct(
        public string $storeSlug,
        public array $lines,
        public string $customerName,
        public string $customerEmail,
        public string $customerPhone,
        public ?string $comment,
    ) {
    }
}
