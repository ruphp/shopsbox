<?php

declare(strict_types=1);

namespace App\Moderation\Application\Contracts;

use App\Moderation\Application\Dto\ModerationProductView;
use App\Moderation\Application\Dto\ModerationStoreView;

interface ModerationQueueRepository
{
    /**
     * @return list<ModerationProductView>
     */
    public function pendingProducts(): array;

    /**
     * @return list<ModerationStoreView>
     */
    public function pendingStores(): array;

    public function reviewProduct(string $productId, string $decision, string $reason, ?string $reviewedBy): bool;

    public function reviewStore(string $storeId, string $decision, string $reason, ?string $reviewedBy): bool;

    public function logAction(string $itemType, string $itemId, string $decision, string $reason, ?string $moderatorId): void;
}
