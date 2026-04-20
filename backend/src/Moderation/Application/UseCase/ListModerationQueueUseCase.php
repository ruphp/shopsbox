<?php

declare(strict_types=1);

namespace App\Moderation\Application\UseCase;

use App\Moderation\Application\Contracts\ModerationQueueRepository;
use App\Moderation\Application\Dto\ModerationQueueView;

final readonly class ListModerationQueueUseCase
{
    public function __construct(private ModerationQueueRepository $repository)
    {
    }

    public function execute(): ModerationQueueView
    {
        return new ModerationQueueView(
            $this->repository->pendingProducts(),
            $this->repository->pendingStores(),
        );
    }
}
