<?php

declare(strict_types=1);

namespace App\Catalog\Domain;

enum ProductPublicationStatus: string
{
    case DRAFT = 'draft';
    case PENDING_REVIEW = 'pending_review';
    case PUBLISHED = 'published';
    case REJECTED = 'rejected';
    case BLOCKED = 'blocked';

    public function canTransitionTo(self $target): bool
    {
        if ($this === $target) {
            return true;
        }

        return match ($this) {
            self::DRAFT => in_array($target, [self::PENDING_REVIEW], true),
            self::PENDING_REVIEW => in_array($target, [self::PUBLISHED, self::REJECTED, self::BLOCKED], true),
            self::PUBLISHED => in_array($target, [self::PENDING_REVIEW, self::BLOCKED], true),
            self::REJECTED => in_array($target, [self::PENDING_REVIEW, self::BLOCKED], true),
            self::BLOCKED => false,
        };
    }
}
