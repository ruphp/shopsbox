<?php

declare(strict_types=1);

namespace App\Moderation\Application\Dto;

final readonly class ModerationProductView
{
    public function __construct(
        public string $id,
        public string $storeId,
        public string $storeName,
        public string $name,
        public string $slug,
        public string $status,
        public string $reason,
        public ?string $reviewReason,
        public ?string $submittedAt,
    ) {
    }
}
