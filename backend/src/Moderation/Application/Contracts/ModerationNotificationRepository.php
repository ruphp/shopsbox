<?php

declare(strict_types=1);

namespace App\Moderation\Application\Contracts;

interface ModerationNotificationRepository
{
    public function createIfMissing(string $itemType, string $itemId, string $reason): void;
}
