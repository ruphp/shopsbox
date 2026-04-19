<?php

declare(strict_types=1);

namespace App\Storefront\Application\Exception;

use RuntimeException;

final class StorefrontProductNotFound extends RuntimeException
{
    public static function bySlug(string $productSlug): self
    {
        return new self(sprintf('Product "%s" was not found in storefront.', $productSlug));
    }
}
