<?php

declare(strict_types=1);

namespace App\Storefront\Application\Exception;

use RuntimeException;

final class StorefrontCategoryNotFound extends RuntimeException
{
    public static function bySlug(string $slug): self
    {
        return new self(sprintf('Storefront category "%s" was not found.', $slug));
    }
}
