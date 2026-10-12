<?php

declare(strict_types=1);

namespace App\Sales\Application\UseCase;

use App\Sales\Application\Contracts\SalesRepository;
use App\Sales\Application\Dto\CartLineInput;
use App\Sales\Application\Dto\CartView;

final readonly class BuildCartUseCase
{
    public function __construct(private SalesRepository $salesRepository)
    {
    }

    /** @param list<CartLineInput> $lines */
    public function execute(string $storeSlug, array $lines): ?CartView
    {
        return $this->salesRepository->buildCart($storeSlug, $this->normalizeLines($lines));
    }

    /**
     * @param list<CartLineInput> $lines
     * @return list<CartLineInput>
     */
    private function normalizeLines(array $lines): array
    {
        $normalized = [];
        foreach ($lines as $line) {
            $quantity = max(0, min(99, $line->quantity));
            if ($quantity === 0) {
                continue;
            }

            $key = $line->productId . ':' . ($line->variantId ?? 'base');
            $normalized[$key] = new CartLineInput($line->productId, $line->variantId, $quantity);
        }

        return array_values($normalized);
    }
}
