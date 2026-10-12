<?php

declare(strict_types=1);

namespace App\Sales\Application\Contracts;

use App\Sales\Application\Dto\CartLineInput;
use App\Sales\Application\Dto\CartView;
use App\Sales\Application\Dto\CheckoutInput;
use App\Sales\Application\Dto\OrderView;

interface SalesRepository
{
    /** @param list<CartLineInput> $lines */
    public function buildCart(string $storeSlug, array $lines): ?CartView;

    public function create(string $id, string $number, CheckoutInput $input, CartView $cart): OrderView;

    /** @return list<OrderView> */
    public function listByOwnerEmail(string $ownerEmail): array;
}
