<?php

declare(strict_types=1);

namespace App\Storefront\Application\Exception;

use RuntimeException;

final class StorefrontStoreNotFound extends RuntimeException
{
    public static function bySlug(string $storeSlug): self
    {
        return new self(sprintf('Store "%s" was not found.', $storeSlug));
    }
}
