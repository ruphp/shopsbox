<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Domain;

use App\Catalog\Domain\ProductStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ProductStatusTest extends TestCase
{
    #[DataProvider('allowedTransitions')]
    public function testItAllowsExpectedTransitions(ProductStatus $current, ProductStatus $target): void
    {
        self::assertTrue($current->canTransitionTo($target));
    }

    #[DataProvider('deniedTransitions')]
    public function testItDeniesExpectedTransitions(ProductStatus $current, ProductStatus $target): void
    {
        self::assertFalse($current->canTransitionTo($target));
    }

    /**
     * @return iterable<string, array{ProductStatus, ProductStatus}>
     */
    public static function allowedTransitions(): iterable
    {
        yield 'draft to draft' => [ProductStatus::DRAFT, ProductStatus::DRAFT];
        yield 'draft to active' => [ProductStatus::DRAFT, ProductStatus::ACTIVE];
        yield 'draft to archived' => [ProductStatus::DRAFT, ProductStatus::ARCHIVED];
        yield 'active to active' => [ProductStatus::ACTIVE, ProductStatus::ACTIVE];
        yield 'active to draft' => [ProductStatus::ACTIVE, ProductStatus::DRAFT];
        yield 'active to archived' => [ProductStatus::ACTIVE, ProductStatus::ARCHIVED];
        yield 'archived to archived' => [ProductStatus::ARCHIVED, ProductStatus::ARCHIVED];
    }

    /**
     * @return iterable<string, array{ProductStatus, ProductStatus}>
     */
    public static function deniedTransitions(): iterable
    {
        yield 'archived to draft' => [ProductStatus::ARCHIVED, ProductStatus::DRAFT];
        yield 'archived to active' => [ProductStatus::ARCHIVED, ProductStatus::ACTIVE];
    }
}
