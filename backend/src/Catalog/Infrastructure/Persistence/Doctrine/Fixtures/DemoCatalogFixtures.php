<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Fixtures;

use App\Catalog\Domain\ProductStatus;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\Category;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\Product;
use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\Store;
use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\Tenant;
use App\Tenant\Infrastructure\Persistence\Doctrine\Fixtures\DemoTenantFixtures;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class DemoCatalogFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $tenant = $this->getReference(DemoTenantFixtures::DEMO_TENANT_REFERENCE, Tenant::class);
        $store = $this->getReference(DemoTenantFixtures::DEMO_STORE_REFERENCE, Store::class);

        $category = new Category(
            '55555555-0001-4555-8555-555555555555',
            $tenant,
            $store,
            'Demo Category',
            'demo-category',
        );
        $manager->persist($category);

        foreach ($this->products() as $productData) {
            $manager->persist(new Product(
                $productData['id'],
                $tenant,
                $store,
                $category,
                $productData['name'],
                $productData['slug'],
                $productData['description'],
                $productData['status'],
            ));
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            DemoTenantFixtures::class,
        ];
    }

    /**
     * @return list<array{id: string, name: string, slug: string, description: string, status: ProductStatus}>
     */
    private function products(): array
    {
        return [
            [
                'id' => '66666666-0001-4666-8666-666666666666',
                'name' => 'Demo Hoodie',
                'slug' => 'demo-hoodie',
                'description' => 'Теплый худи для первой витрины ShopsBox.',
                'status' => ProductStatus::ACTIVE,
            ],
            [
                'id' => '66666666-0002-4666-8666-666666666666',
                'name' => 'Demo Mug',
                'slug' => 'demo-mug',
                'description' => 'Кружка для проверки публичной карточки товара.',
                'status' => ProductStatus::ACTIVE,
            ],
            [
                'id' => '66666666-0003-4666-8666-666666666666',
                'name' => 'Hidden Draft Product',
                'slug' => 'hidden-draft-product',
                'description' => 'Этот товар не должен отображаться на витрине.',
                'status' => ProductStatus::DRAFT,
            ],
        ];
    }
}
