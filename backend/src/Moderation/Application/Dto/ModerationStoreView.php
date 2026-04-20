<?php

declare(strict_types=1);

namespace App\Moderation\Application\Dto;

final readonly class ModerationStoreView
{
    public function __construct(
        public string $id,
        public string $name,
        public ?string $publicSubdomain,
        public string $status,
        public string $reason,
        public ?string $reviewReason,
        public ?string $submittedAt,
    ) {
    }
}
