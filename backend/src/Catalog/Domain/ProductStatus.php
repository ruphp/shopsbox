<?php

declare(strict_types=1);

namespace App\Catalog\Domain;

enum ProductStatus: string
{
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case ARCHIVED = 'archived';

    public function canTransitionTo(self $target): bool
    {
        if ($this === $target) {
            return true;
        }

        return match ($this) {
            self::DRAFT => in_array($target, [self::ACTIVE, self::ARCHIVED], true),
            self::ACTIVE => in_array($target, [self::DRAFT, self::ARCHIVED], true),
            self::ARCHIVED => false,
        };
    }
}
