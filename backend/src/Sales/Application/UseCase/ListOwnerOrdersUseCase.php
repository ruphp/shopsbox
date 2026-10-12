<?php

declare(strict_types=1);

namespace App\Sales\Application\UseCase;

use App\Sales\Application\Contracts\SalesRepository;

final readonly class ListOwnerOrdersUseCase
{
    public function __construct(private SalesRepository $salesRepository)
    {
    }

    public function execute(string $ownerEmail): array
    {
        return $this->salesRepository->listByOwnerEmail(strtolower(trim($ownerEmail)));
    }
}
