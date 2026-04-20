<?php

declare(strict_types=1);

namespace App\Moderation\Application\Dto;

final readonly class ModeratorView
{
    public function __construct(
        public string $userId,
        public string $email,
        public string $name,
        public string $assignedAt,
    ) {
    }
}
