<?php

declare(strict_types=1);

namespace App\Moderation\Application\UseCase;

use App\Moderation\Application\Contracts\EntityFlusher;
use App\Moderation\Application\Contracts\ModerationQueueRepository;
use RuntimeException;

final readonly class ReviewModerationItemUseCase
{
    public function __construct(
        private ModerationQueueRepository $repository,
        private EntityFlusher $flusher,
    ) {
    }

    public function execute(string $type, string $id, string $decision, string $reason, ?string $reviewedBy): void
    {
        if (!in_array($decision, ['approve', 'reject', 'block'], true)) {
            throw new RuntimeException('Unknown moderation decision.');
        }

        if (in_array($decision, ['reject', 'block'], true) && trim($reason) === '') {
            throw new RuntimeException('Reason is required for reject or block.');
        }

        $reviewed = match ($type) {
            'product' => $this->repository->reviewProduct($id, $decision, trim($reason), $reviewedBy),
            'store' => $this->repository->reviewStore($id, $decision, trim($reason), $reviewedBy),
            default => false,
        };

        if (!$reviewed) {
            throw new RuntimeException('Moderation item not found.');
        }

        $this->repository->logAction($type, $id, $decision, trim($reason), $reviewedBy);
        $this->flusher->flush();
    }
}
