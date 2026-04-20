<?php

declare(strict_types=1);

namespace App\Moderation\Application\Dto;

final readonly class ModerationQueueView
{
    /**
     * @param list<ModerationProductView> $products
     * @param list<ModerationStoreView> $stores
     */
    public function __construct(
        public array $products,
        public array $stores,
    ) {
    }
}
