<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Fixtures;

use App\Catalog\Domain\ProductStatus;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\Category;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\Product;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\ProductAttribute;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\ProductImage;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\ProductOptionGroup;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\ProductOptionValue;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\ProductVariant;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\ProductVariantOptionValue;
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

        $categories = $this->loadCategories($manager, $tenant, $store);
        $products = $this->loadProducts($manager, $tenant, $store, $categories);

        $this->loadProductImages($manager, $products);
        $this->loadProductAttributes($manager, $tenant, $store, $products);
        $this->loadVariantExamples($manager, $tenant, $store, $products);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            DemoTenantFixtures::class,
        ];
    }

    /**
     * @return array<string, Category>
     */
    private function loadCategories(ObjectManager $manager, Tenant $tenant, Store $store): array
    {
        $categories = [];

        foreach ($this->categoryRows() as $row) {
            $category = $manager->find(Category::class, $row['id']);
            if (!$category instanceof Category) {
                $category = new Category($row['id'], $tenant, $store, $row['name'], $row['slug']);
                $manager->persist($category);
            }

            $categories[$row['key']] = $category;
        }

        return $categories;
    }

    /**
     * @param array<string, Category> $categories
     *
     * @return array<string, Product>
     */
    private function loadProducts(ObjectManager $manager, Tenant $tenant, Store $store, array $categories): array
    {
        $products = [];

        foreach ($this->productRows() as $row) {
            $product = $manager->find(Product::class, $row['id']);
            if (!$product instanceof Product) {
                $product = new Product(
                    $row['id'],
                    $tenant,
                    $store,
                    $categories[$row['category']],
                    $row['name'],
                    $row['slug'],
                    $row['description'],
                    $row['status'],
                );
                $manager->persist($product);
            }

            $products[$row['key']] = $product;
        }

        return $products;
    }

    /**
     * @param array<string, Product> $products
     */
    private function loadProductImages(ObjectManager $manager, array $products): void
    {
        foreach ($this->imageRows() as $row) {
            if ($manager->find(ProductImage::class, $row['id']) instanceof ProductImage) {
                continue;
            }

            $manager->persist(new ProductImage(
                $row['id'],
                $products[$row['product']],
                $row['key'],
                $row['publicUrl'],
                'image/jpeg',
                120000,
            ));
        }
    }

    /**
     * @param array<string, Product> $products
     */
    private function loadProductAttributes(ObjectManager $manager, Tenant $tenant, Store $store, array $products): void
    {
        foreach ($this->attributeRows() as $row) {
            if ($manager->find(ProductAttribute::class, $row['id']) instanceof ProductAttribute) {
                continue;
            }

            $manager->persist(new ProductAttribute(
                $row['id'],
                $tenant,
                $store,
                $products[$row['product']],
                $row['code'],
                $row['name'],
                $row['value'],
                $row['position'],
            ));
        }
    }

    /**
     * @param array<string, Product> $products
     */
    private function loadVariantExamples(ObjectManager $manager, Tenant $tenant, Store $store, array $products): void
    {
        $hoodieSize = $this->optionGroup(
            $manager,
            '77777777-0101-4777-8777-777777777777',
            $tenant,
            $store,
            $products['hoodie'],
            'size',
            'Размер',
            10,
        );
        $hoodieColor = $this->optionGroup(
            $manager,
            '77777777-0102-4777-8777-777777777777',
            $tenant,
            $store,
            $products['hoodie'],
            'color',
            'Цвет',
            20,
        );

        $sizeM = $this->optionValue($manager, '77777777-0201-4777-8777-777777777777', $tenant, $store, $hoodieSize, 'm', 'M', 10);
        $sizeL = $this->optionValue($manager, '77777777-0202-4777-8777-777777777777', $tenant, $store, $hoodieSize, 'l', 'L', 20);
        $colorGraphite = $this->optionValue($manager, '77777777-0203-4777-8777-777777777777', $tenant, $store, $hoodieColor, 'graphite', 'Графит', 10);
        $colorMilk = $this->optionValue($manager, '77777777-0204-4777-8777-777777777777', $tenant, $store, $hoodieColor, 'milk', 'Молочный', 20);

        $this->variant($manager, '77777777-0301-4777-8777-777777777777', $tenant, $store, $products['hoodie'], 'M / графит', 'HD-GRF-M', null, true, [$sizeM, $colorGraphite]);
        $this->variant($manager, '77777777-0302-4777-8777-777777777777', $tenant, $store, $products['hoodie'], 'L / графит', 'HD-GRF-L', '300.00', true, [$sizeL, $colorGraphite]);
        $this->variant($manager, '77777777-0303-4777-8777-777777777777', $tenant, $store, $products['hoodie'], 'M / молочный', 'HD-MLK-M', null, true, [$sizeM, $colorMilk]);

        $bundleType = $this->optionGroup(
            $manager,
            '77777777-0103-4777-8777-777777777777',
            $tenant,
            $store,
            $products['starterKit'],
            'bundle',
            'Комплектация',
            10,
        );
        $baseBundle = $this->optionValue($manager, '77777777-0205-4777-8777-777777777777', $tenant, $store, $bundleType, 'base', 'Базовая', 10);
        $giftBundle = $this->optionValue($manager, '77777777-0206-4777-8777-777777777777', $tenant, $store, $bundleType, 'gift', 'Подарочная', 20);

        $this->variant($manager, '77777777-0304-4777-8777-777777777777', $tenant, $store, $products['starterKit'], 'Базовая комплектация', 'KIT-BASE', null, true, [$baseBundle]);
        $this->variant($manager, '77777777-0305-4777-8777-777777777777', $tenant, $store, $products['starterKit'], 'Подарочная комплектация', 'KIT-GIFT', '450.00', true, [$giftBundle]);
    }

    private function optionGroup(
        ObjectManager $manager,
        string $id,
        Tenant $tenant,
        Store $store,
        Product $product,
        string $code,
        string $name,
        int $position,
    ): ProductOptionGroup {
        $group = $manager->find(ProductOptionGroup::class, $id);
        if ($group instanceof ProductOptionGroup) {
            return $group;
        }

        $group = new ProductOptionGroup($id, $tenant, $store, $product, $code, $name, $position);
        $manager->persist($group);

        return $group;
    }

    private function optionValue(
        ObjectManager $manager,
        string $id,
        Tenant $tenant,
        Store $store,
        ProductOptionGroup $group,
        string $code,
        string $value,
        int $position,
    ): ProductOptionValue {
        $optionValue = $manager->find(ProductOptionValue::class, $id);
        if ($optionValue instanceof ProductOptionValue) {
            return $optionValue;
        }

        $optionValue = new ProductOptionValue($id, $tenant, $store, $group, $code, $value, $position);
        $manager->persist($optionValue);

        return $optionValue;
    }

    /**
     * @param list<ProductOptionValue> $values
     */
    private function variant(
        ObjectManager $manager,
        string $id,
        Tenant $tenant,
        Store $store,
        Product $product,
        string $name,
        string $sku,
        ?string $priceAdjustment,
        bool $active,
        array $values,
    ): void {
        $variant = $manager->find(ProductVariant::class, $id);
        if (!$variant instanceof ProductVariant) {
            $variant = new ProductVariant($id, $tenant, $store, $product, $name, $sku, $priceAdjustment, $active);
            $manager->persist($variant);
        }

        foreach ($values as $index => $value) {
            $linkId = substr($id, 0, 34) . sprintf('%02d', $index + 1);
            if (!$manager->find(ProductVariantOptionValue::class, $linkId) instanceof ProductVariantOptionValue) {
                $manager->persist(new ProductVariantOptionValue($linkId, $variant, $value));
            }
        }
    }

    /**
     * @return list<array{key: string, id: string, name: string, slug: string}>
     */
    private function categoryRows(): array
    {
        return [
            ['key' => 'apparel', 'id' => '55555555-0101-4555-8555-555555555555', 'name' => 'Одежда', 'slug' => 'apparel'],
            ['key' => 'home', 'id' => '55555555-0102-4555-8555-555555555555', 'name' => 'Дом и уют', 'slug' => 'home'],
            ['key' => 'digital', 'id' => '55555555-0103-4555-8555-555555555555', 'name' => 'Цифровые товары', 'slug' => 'digital'],
            ['key' => 'archive', 'id' => '55555555-0104-4555-8555-555555555555', 'name' => 'Архив демо', 'slug' => 'archive-demo'],
        ];
    }

    /**
     * @return list<array{key: string, id: string, category: string, name: string, slug: string, description: string, status: ProductStatus}>
     */
    private function productRows(): array
    {
        return [
            [
                'key' => 'hoodie',
                'id' => '66666666-0101-4666-8666-666666666666',
                'category' => 'apparel',
                'name' => 'Худи ShopsBox Core',
                'slug' => 'shopsbox-core-hoodie',
                'description' => 'Базовое худи для демонстрации товара с размером, цветом и разницей цены у вариаций.',
                'status' => ProductStatus::ACTIVE,
            ],
            [
                'key' => 'mug',
                'id' => '66666666-0102-4666-8666-666666666666',
                'category' => 'home',
                'name' => 'Кружка для витрины',
                'slug' => 'storefront-mug',
                'description' => 'Простой активный товар без вариаций: нужен как контрольный пример обычной карточки.',
                'status' => ProductStatus::ACTIVE,
            ],
            [
                'key' => 'starterKit',
                'id' => '66666666-0103-4666-8666-666666666666',
                'category' => 'home',
                'name' => 'Стартовый набор магазина',
                'slug' => 'starter-store-kit',
                'description' => 'Товар с опцией комплектации: базовая и подарочная версия.',
                'status' => ProductStatus::ACTIVE,
            ],
            [
                'key' => 'guide',
                'id' => '66666666-0104-4666-8666-666666666666',
                'category' => 'digital',
                'name' => 'Гайд по запуску магазина',
                'slug' => 'launch-guide',
                'description' => 'Цифровой товар без физических опций. Показывает, что каталог не ограничен одеждой.',
                'status' => ProductStatus::ACTIVE,
            ],
            [
                'key' => 'draftLanding',
                'id' => '66666666-0105-4666-8666-666666666666',
                'category' => 'digital',
                'name' => 'Черновик набора баннеров',
                'slug' => 'draft-banner-kit',
                'description' => 'Draft-пример: должен быть в данных, но не должен попадать на публичную витрину.',
                'status' => ProductStatus::DRAFT,
            ],
            [
                'key' => 'archivedPoster',
                'id' => '66666666-0106-4666-8666-666666666666',
                'category' => 'archive',
                'name' => 'Архивный постер',
                'slug' => 'archived-poster',
                'description' => 'Archived-пример: нужен для проверки статусов и будущей админки.',
                'status' => ProductStatus::ARCHIVED,
            ],
        ];
    }

    /**
     * @return list<array{id: string, product: string, key: string, publicUrl: string}>
     */
    private function imageRows(): array
    {
        return [
            ['id' => '88888888-0101-4888-8888-888888888888', 'product' => 'hoodie', 'key' => 'demo-catalog/hoodie.jpg', 'publicUrl' => 'https://picsum.photos/seed/shopsbox-hoodie/900/700'],
            ['id' => '88888888-0102-4888-8888-888888888888', 'product' => 'mug', 'key' => 'demo-catalog/mug.jpg', 'publicUrl' => 'https://picsum.photos/seed/shopsbox-mug/900/700'],
            ['id' => '88888888-0103-4888-8888-888888888888', 'product' => 'starterKit', 'key' => 'demo-catalog/starter-kit.jpg', 'publicUrl' => 'https://picsum.photos/seed/shopsbox-kit/900/700'],
            ['id' => '88888888-0104-4888-8888-888888888888', 'product' => 'guide', 'key' => 'demo-catalog/guide.jpg', 'publicUrl' => 'https://picsum.photos/seed/shopsbox-guide/900/700'],
        ];
    }

    /**
     * @return list<array{id: string, product: string, code: string, name: string, value: string, position: int}>
     */
    private function attributeRows(): array
    {
        return [
            ['id' => '99999999-0101-4999-8999-999999999999', 'product' => 'hoodie', 'code' => 'material', 'name' => 'Материал', 'value' => 'Хлопок 80%, полиэстер 20%', 'position' => 10],
            ['id' => '99999999-0102-4999-8999-999999999999', 'product' => 'hoodie', 'code' => 'care', 'name' => 'Уход', 'value' => 'Деликатная стирка при 30 градусах', 'position' => 20],
            ['id' => '99999999-0103-4999-8999-999999999999', 'product' => 'starterKit', 'code' => 'inside', 'name' => 'Состав набора', 'value' => 'Коробка, открытка, демо-упаковка и инструкция', 'position' => 10],
            ['id' => '99999999-0104-4999-8999-999999999999', 'product' => 'guide', 'code' => 'format', 'name' => 'Формат', 'value' => 'PDF, 24 страницы', 'position' => 10],
        ];
    }
}
