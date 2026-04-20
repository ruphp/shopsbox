<?php

declare(strict_types=1);

namespace App\Storefront\Infrastructure\Persistence\Doctrine\Repository;

use App\Catalog\Domain\ProductStatus;
use App\Catalog\Domain\ProductPublicationStatus;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\Category;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\Product;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\ProductAttribute;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\ProductImage;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\ProductOptionGroup;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\ProductOptionValue;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\ProductVariant;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\ProductVariantOptionValue;
use App\Storefront\Application\Contracts\StorefrontProductRepository;
use App\Storefront\Application\Dto\StorefrontCategoryView;
use App\Storefront\Application\Dto\StorefrontProductAttributeView;
use App\Storefront\Application\Dto\StorefrontProductOptionGroupView;
use App\Storefront\Application\Dto\StorefrontProductOptionValueView;
use App\Storefront\Application\Dto\StorefrontProductVariantView;
use App\Storefront\Application\Dto\StorefrontProductView;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineStorefrontProductRepository implements StorefrontProductRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function listCategoriesByStore(string $storeId): array
    {
        $categories = $this->entityManager->createQueryBuilder()
            ->select('category')
            ->from(Category::class, 'category')
            ->where('IDENTITY(category.store) = :storeId')
            ->setParameter('storeId', $storeId)
            ->orderBy('category.name', 'ASC')
            ->getQuery()
            ->getResult();

        return array_map(fn (Category $category): StorefrontCategoryView => $this->mapCategory($category), $categories);
    }

    public function listActiveByStore(string $storeId): array
    {
        $products = $this->entityManager->createQueryBuilder()
            ->select('product')
            ->from(Product::class, 'product')
            ->where('IDENTITY(product.store) = :storeId')
            ->andWhere('product.status = :status')
            ->andWhere('product.publicationStatus = :publicationStatus')
            ->setParameter('storeId', $storeId)
            ->setParameter('status', ProductStatus::ACTIVE)
            ->setParameter('publicationStatus', ProductPublicationStatus::PUBLISHED)
            ->orderBy('product.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return array_map(fn (Product $product): StorefrontProductView => $this->map($product), $products);
    }

    public function listActiveByStoreAndCategory(string $storeId, string $categorySlug): array
    {
        $products = $this->entityManager->createQueryBuilder()
            ->select('product')
            ->from(Product::class, 'product')
            ->join('product.category', 'category')
            ->where('IDENTITY(product.store) = :storeId')
            ->andWhere('category.slug = :categorySlug')
            ->andWhere('product.status = :status')
            ->andWhere('product.publicationStatus = :publicationStatus')
            ->setParameter('storeId', $storeId)
            ->setParameter('categorySlug', $categorySlug)
            ->setParameter('status', ProductStatus::ACTIVE)
            ->setParameter('publicationStatus', ProductPublicationStatus::PUBLISHED)
            ->orderBy('product.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return array_map(fn (Product $product): StorefrontProductView => $this->map($product), $products);
    }

    public function findCategoryByStoreAndSlug(string $storeId, string $categorySlug): ?StorefrontCategoryView
    {
        $category = $this->entityManager->createQueryBuilder()
            ->select('category')
            ->from(Category::class, 'category')
            ->where('IDENTITY(category.store) = :storeId')
            ->andWhere('category.slug = :categorySlug')
            ->setParameter('storeId', $storeId)
            ->setParameter('categorySlug', $categorySlug)
            ->getQuery()
            ->getOneOrNullResult();

        return $category instanceof Category ? $this->mapCategory($category) : null;
    }

    public function findActiveByStoreAndSlug(string $storeId, string $productSlug): ?StorefrontProductView
    {
        $product = $this->entityManager->createQueryBuilder()
            ->select('product')
            ->from(Product::class, 'product')
            ->where('IDENTITY(product.store) = :storeId')
            ->andWhere('product.slug = :productSlug')
            ->andWhere('product.status = :status')
            ->andWhere('product.publicationStatus = :publicationStatus')
            ->setParameter('storeId', $storeId)
            ->setParameter('productSlug', $productSlug)
            ->setParameter('status', ProductStatus::ACTIVE)
            ->setParameter('publicationStatus', ProductPublicationStatus::PUBLISHED)
            ->getQuery()
            ->getOneOrNullResult();

        return $product instanceof Product ? $this->map($product) : null;
    }

    private function map(Product $product): StorefrontProductView
    {
        return new StorefrontProductView(
            $product->id(),
            $product->store()->id(),
            $product->name(),
            $product->slug(),
            $product->description(),
            $product->category() instanceof Category ? $this->mapCategory($product->category()) : null,
            $this->firstImageUrl($product),
            $this->attributes($product),
            $this->optionGroups($product),
            $this->variants($product),
        );
    }

    private function mapCategory(Category $category): StorefrontCategoryView
    {
        return new StorefrontCategoryView($category->id(), $category->name(), $category->slug());
    }

    private function firstImageUrl(Product $product): ?string
    {
        $image = $this->entityManager->createQueryBuilder()
            ->select('image')
            ->from(ProductImage::class, 'image')
            ->where('image.product = :product')
            ->setParameter('product', $product)
            ->orderBy('image.createdAt', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $image instanceof ProductImage ? $image->publicUrl() : null;
    }

    /**
     * @return list<StorefrontProductAttributeView>
     */
    private function attributes(Product $product): array
    {
        $attributes = $this->entityManager->createQueryBuilder()
            ->select('attribute')
            ->from(ProductAttribute::class, 'attribute')
            ->where('attribute.product = :product')
            ->setParameter('product', $product)
            ->orderBy('attribute.position', 'ASC')
            ->getQuery()
            ->getResult();

        return array_map(
            fn (ProductAttribute $attribute): StorefrontProductAttributeView => new StorefrontProductAttributeView($attribute->name(), $attribute->value()),
            $attributes,
        );
    }

    /**
     * @return list<StorefrontProductOptionGroupView>
     */
    private function optionGroups(Product $product): array
    {
        $groups = $this->entityManager->createQueryBuilder()
            ->select('optionGroup')
            ->from(ProductOptionGroup::class, 'optionGroup')
            ->where('optionGroup.product = :product')
            ->setParameter('product', $product)
            ->orderBy('optionGroup.position', 'ASC')
            ->getQuery()
            ->getResult();

        return array_map(function (ProductOptionGroup $group): StorefrontProductOptionGroupView {
            $values = $this->entityManager->createQueryBuilder()
                ->select('optionValue')
                ->from(ProductOptionValue::class, 'optionValue')
                ->where('optionValue.optionGroup = :optionGroup')
                ->setParameter('optionGroup', $group)
                ->orderBy('optionValue.position', 'ASC')
                ->getQuery()
                ->getResult();

            return new StorefrontProductOptionGroupView(
                $group->name(),
                array_map(
                    fn (ProductOptionValue $value): StorefrontProductOptionValueView => new StorefrontProductOptionValueView($value->code(), $value->value()),
                    $values,
                ),
            );
        }, $groups);
    }

    /**
     * @return list<StorefrontProductVariantView>
     */
    private function variants(Product $product): array
    {
        $variants = $this->entityManager->createQueryBuilder()
            ->select('variant')
            ->from(ProductVariant::class, 'variant')
            ->where('variant.product = :product')
            ->andWhere('variant.active = true')
            ->setParameter('product', $product)
            ->orderBy('variant.name', 'ASC')
            ->getQuery()
            ->getResult();

        return array_map(fn (ProductVariant $variant): StorefrontProductVariantView => new StorefrontProductVariantView(
            $variant->name(),
            $variant->sku(),
            $variant->priceAdjustment(),
            $this->variantOptionValues($variant),
        ), $variants);
    }

    /**
     * @return list<string>
     */
    private function variantOptionValues(ProductVariant $variant): array
    {
        $links = $this->entityManager->createQueryBuilder()
            ->select('link')
            ->from(ProductVariantOptionValue::class, 'link')
            ->join('link.optionValue', 'optionValue')
            ->where('link.variant = :variant')
            ->setParameter('variant', $variant)
            ->orderBy('optionValue.position', 'ASC')
            ->getQuery()
            ->getResult();

        return array_map(
            fn (ProductVariantOptionValue $link): string => $link->optionValue()->value(),
            $links,
        );
    }
}
