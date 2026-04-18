<?php

declare(strict_types=1);

namespace App\Catalog\Application\Exception;

use RuntimeException;

final class ProductNotFound extends RuntimeException
{
    public static function byId(string $productId): self
    {
        return new self(sprintf('Product "%s" was not found.', $productId));
    }
}
