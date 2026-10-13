<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Infrastructure\Persistence\Doctrine\Entity;

use App\Catalog\Domain\ProductPublicationStatus;
use App\Catalog\Domain\ProductStatus;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\Product;
use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\Store;
use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\Tenant;
use PHPUnit\Framework\TestCase;

final class ProductTest extends TestCase
{
    public function testActivatingDraftPublishesItOnStorefront(): void
    {
        $product = $this->product();

        $product->changeStatus(ProductStatus::ACTIVE);

        self::assertSame(ProductStatus::ACTIVE, $product->status());
        self::assertSame(ProductPublicationStatus::PUBLISHED, $product->publicationStatus());
    }

    public function testActivatingBlockedProductDoesNotBypassModeration(): void
    {
        $product = $this->product();
        $product->blockPublication(null, 'Нарушение правил');

        $product->changeStatus(ProductStatus::ACTIVE);

        self::assertSame(ProductPublicationStatus::BLOCKED, $product->publicationStatus());
    }

    private function product(): Product
    {
        $tenant = new Tenant('11111111-1111-4111-8111-111111111111', 'Test', 'active', 'test@example.test');
        $store = new Store('22222222-2222-4222-8222-222222222222', $tenant, 'Test', 'test', 'test.shopsbox.local', 'active', 'RUB', 'Asia/Yekaterinburg');

        return new Product('33333333-3333-4333-8333-333333333333', $tenant, $store, null, 'Draft', 'draft', null, ProductStatus::DRAFT, '100.00');
    }
}
