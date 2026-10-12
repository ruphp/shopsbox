<?php

declare(strict_types=1);

namespace App\Sales\Application\Dto;

final readonly class OrderView
{
    /** @param list<OrderItemView> $items */
    public function __construct(
        public string $id,
        public string $number,
        public string $status,
        public string $customerName,
        public string $customerEmail,
        public string $customerPhone,
        public ?string $comment,
        public int $totalMinor,
        public string $currency,
        public string $createdAt,
        public array $items,
    ) {
    }
}
