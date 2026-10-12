<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Repository;

use App\Catalog\Application\Contracts\ProductStructureRepository;
use App\Catalog\Application\Dto\ProductStructureView;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\Product;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\ProductAttribute;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\ProductOptionGroup;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\ProductOptionValue;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\ProductVariant;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\ProductVariantOptionValue;
use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\Store;
use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\Tenant;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\Uid\Uuid;

final readonly class DoctrineProductStructureRepository implements ProductStructureRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function view(string $storeId, string $productId): ProductStructureView
    {
        $attributes = $this->entityManager->createQueryBuilder()
            ->select('attribute')
            ->from(ProductAttribute::class, 'attribute')
            ->join('attribute.product', 'product')
            ->where('product.id = :productId')
            ->andWhere('IDENTITY(product.store) = :storeId')
            ->setParameter('productId', $productId)
            ->setParameter('storeId', $storeId)
            ->orderBy('attribute.position', 'ASC')
            ->getQuery()->getResult();

        $groups = $this->groups($storeId, $productId);
        $variants = $this->entityManager->createQueryBuilder()
            ->select('variant')
            ->from(ProductVariant::class, 'variant')
            ->join('variant.product', 'product')
            ->where('product.id = :productId')
            ->andWhere('IDENTITY(product.store) = :storeId')
            ->setParameter('productId', $productId)
            ->setParameter('storeId', $storeId)
            ->orderBy('variant.name', 'ASC')
            ->getQuery()->getResult();

        return new ProductStructureView(
            array_map(static fn (ProductAttribute $attribute): array => [
                'id' => $attribute->id(),
                'name' => $attribute->name(),
                'value' => $attribute->value(),
                'position' => $attribute->position(),
            ], $attributes),
            array_map(fn (ProductOptionGroup $group): array => [
                'id' => $group->id(),
                'name' => $group->name(),
                'values' => array_map(static fn (ProductOptionValue $value): array => [
                    'id' => $value->id(),
                    'value' => $value->value(),
                ], $this->values($group)),
            ], $groups),
            array_map(fn (ProductVariant $variant): array => [
                'id' => $variant->id(),
                'name' => $variant->name(),
                'sku' => $variant->sku(),
                'priceAdjustment' => $variant->priceAdjustment(),
                'active' => $variant->active(),
                'optionValueIds' => $this->variantValueIds($variant),
            ], $variants),
        );
    }

    public function createAttribute(string $tenantId, string $storeId, string $productId, string $name, string $value): void
    {
        $product = $this->product($storeId, $productId);
        $id = $this->id();
        $this->entityManager->persist(new ProductAttribute(
            $id,
            $this->entityManager->getReference(Tenant::class, $tenantId),
            $this->entityManager->getReference(Store::class, $storeId),
            $product,
            'attribute-'.$this->shortCode($id),
            $name,
            $value,
            $this->nextPosition(ProductAttribute::class, 'product', $product),
        ));
    }

    public function updateAttribute(string $storeId, string $productId, string $attributeId, string $name, string $value): bool
    {
        $attribute = $this->attribute($storeId, $productId, $attributeId);
        if (!$attribute instanceof ProductAttribute) {
            return false;
        }
        $attribute->update($name, $value);

        return true;
    }

    public function deleteAttribute(string $storeId, string $productId, string $attributeId): bool
    {
        return $this->remove($this->attribute($storeId, $productId, $attributeId));
    }

    public function createOptionGroup(string $tenantId, string $storeId, string $productId, string $name): void
    {
        $product = $this->product($storeId, $productId);
        $id = $this->id();
        $this->entityManager->persist(new ProductOptionGroup(
            $id,
            $this->entityManager->getReference(Tenant::class, $tenantId),
            $this->entityManager->getReference(Store::class, $storeId),
            $product,
            'option-'.$this->shortCode($id),
            $name,
            $this->nextPosition(ProductOptionGroup::class, 'product', $product),
        ));
    }

    public function updateOptionGroup(string $storeId, string $productId, string $groupId, string $name): bool
    {
        $group = $this->group($storeId, $productId, $groupId);
        if (!$group instanceof ProductOptionGroup) {
            return false;
        }
        $group->rename($name);

        return true;
    }

    public function deleteOptionGroup(string $storeId, string $productId, string $groupId): bool
    {
        return $this->remove($this->group($storeId, $productId, $groupId));
    }

    public function createOptionValue(string $tenantId, string $storeId, string $productId, string $groupId, string $value): bool
    {
        $group = $this->group($storeId, $productId, $groupId);
        if (!$group instanceof ProductOptionGroup) {
            return false;
        }
        $id = $this->id();
        $this->entityManager->persist(new ProductOptionValue(
            $id,
            $this->entityManager->getReference(Tenant::class, $tenantId),
            $this->entityManager->getReference(Store::class, $storeId),
            $group,
            'value-'.$this->shortCode($id),
            $value,
            $this->nextPosition(ProductOptionValue::class, 'optionGroup', $group),
        ));

        return true;
    }

    public function updateOptionValue(string $storeId, string $productId, string $valueId, string $value): bool
    {
        $optionValue = $this->value($storeId, $productId, $valueId);
        if (!$optionValue instanceof ProductOptionValue) {
            return false;
        }
        $optionValue->rename($value);

        return true;
    }

    public function deleteOptionValue(string $storeId, string $productId, string $valueId): bool
    {
        return $this->remove($this->value($storeId, $productId, $valueId));
    }

    public function createVariant(string $tenantId, string $storeId, string $productId, string $name, string $sku, ?string $priceAdjustment, bool $active, array $optionValueIds): void
    {
        $product = $this->product($storeId, $productId);
        $this->assertSkuAvailable($product, $sku);
        $variant = new ProductVariant(
            $this->id(),
            $this->entityManager->getReference(Tenant::class, $tenantId),
            $this->entityManager->getReference(Store::class, $storeId),
            $product,
            $name,
            $sku,
            $priceAdjustment,
            $active,
        );
        $this->entityManager->persist($variant);
        $this->replaceVariantValues($storeId, $productId, $variant, $optionValueIds);
    }

    public function updateVariant(string $storeId, string $productId, string $variantId, string $name, string $sku, ?string $priceAdjustment, bool $active, array $optionValueIds): bool
    {
        $variant = $this->variant($storeId, $productId, $variantId);
        if (!$variant instanceof ProductVariant) {
            return false;
        }
        $this->assertSkuAvailable($variant->product(), $sku, $variantId);
        $variant->update($name, $sku, $priceAdjustment, $active);
        $this->replaceVariantValues($storeId, $productId, $variant, $optionValueIds);

        return true;
    }

    public function deleteVariant(string $storeId, string $productId, string $variantId): bool
    {
        return $this->remove($this->variant($storeId, $productId, $variantId));
    }

    private function product(string $storeId, string $productId): Product
    {
        $product = $this->entityManager->createQueryBuilder()->select('product')->from(Product::class, 'product')
            ->where('product.id = :productId')->andWhere('IDENTITY(product.store) = :storeId')
            ->setParameter('productId', $productId)->setParameter('storeId', $storeId)
            ->getQuery()->getOneOrNullResult();
        if (!$product instanceof Product) {
            throw new InvalidArgumentException('Товар не найден.');
        }

        return $product;
    }

    /** @return list<ProductOptionGroup> */
    private function groups(string $storeId, string $productId): array
    {
        return $this->entityManager->createQueryBuilder()->select('item')->from(ProductOptionGroup::class, 'item')
            ->join('item.product', 'product')->where('product.id = :productId')->andWhere('IDENTITY(product.store) = :storeId')
            ->setParameter('productId', $productId)->setParameter('storeId', $storeId)->orderBy('item.position', 'ASC')
            ->getQuery()->getResult();
    }

    /** @return list<ProductOptionValue> */
    private function values(ProductOptionGroup $group): array
    {
        return $this->entityManager->getRepository(ProductOptionValue::class)->findBy(['optionGroup' => $group], ['position' => 'ASC']);
    }

    /** @return list<string> */
    private function variantValueIds(ProductVariant $variant): array
    {
        $links = $this->entityManager->getRepository(ProductVariantOptionValue::class)->findBy(['variant' => $variant]);

        return array_map(static fn (ProductVariantOptionValue $link): string => $link->optionValue()->id(), $links);
    }

    private function attribute(string $storeId, string $productId, string $id): ?ProductAttribute
    {
        return $this->owned(ProductAttribute::class, 'product', $storeId, $productId, $id);
    }

    private function group(string $storeId, string $productId, string $id): ?ProductOptionGroup
    {
        return $this->owned(ProductOptionGroup::class, 'product', $storeId, $productId, $id);
    }

    private function value(string $storeId, string $productId, string $id): ?ProductOptionValue
    {
        $value = $this->entityManager->createQueryBuilder()->select('item')->from(ProductOptionValue::class, 'item')
            ->join('item.optionGroup', 'optionGroup')->join('optionGroup.product', 'product')
            ->where('item.id = :id')->andWhere('product.id = :productId')->andWhere('IDENTITY(product.store) = :storeId')
            ->setParameter('id', $id)->setParameter('productId', $productId)->setParameter('storeId', $storeId)
            ->getQuery()->getOneOrNullResult();

        return $value instanceof ProductOptionValue ? $value : null;
    }

    private function variant(string $storeId, string $productId, string $id): ?ProductVariant
    {
        return $this->owned(ProductVariant::class, 'product', $storeId, $productId, $id);
    }

    /** @template T of object @param class-string<T> $class @return T|null */
    private function owned(string $class, string $relation, string $storeId, string $productId, string $id): ?object
    {
        $item = $this->entityManager->createQueryBuilder()->select('item')->from($class, 'item')
            ->join('item.'.$relation, 'product')->where('item.id = :id')->andWhere('product.id = :productId')
            ->andWhere('IDENTITY(product.store) = :storeId')->setParameter('id', $id)
            ->setParameter('productId', $productId)->setParameter('storeId', $storeId)->getQuery()->getOneOrNullResult();

        return is_object($item) ? $item : null;
    }

    private function remove(?object $entity): bool
    {
        if ($entity === null) {
            return false;
        }
        $this->entityManager->remove($entity);

        return true;
    }

    private function nextPosition(string $class, string $relation, object $owner): int
    {
        return ((int) $this->entityManager->createQueryBuilder()->select('COUNT(item.id)')->from($class, 'item')
            ->where('item.'.$relation.' = :owner')->setParameter('owner', $owner)->getQuery()->getSingleScalarResult() + 1) * 10;
    }

    private function assertSkuAvailable(Product $product, string $sku, ?string $exceptId = null): void
    {
        $query = $this->entityManager->createQueryBuilder()->select('COUNT(variant.id)')->from(ProductVariant::class, 'variant')
            ->where('variant.product = :product')->andWhere('variant.sku = :sku')->setParameter('product', $product)->setParameter('sku', $sku);
        if ($exceptId !== null) {
            $query->andWhere('variant.id != :exceptId')->setParameter('exceptId', $exceptId);
        }
        if ((int) $query->getQuery()->getSingleScalarResult() > 0) {
            throw new InvalidArgumentException('Такой SKU уже используется у этого товара.');
        }
    }

    /** @param list<string> $valueIds */
    private function replaceVariantValues(string $storeId, string $productId, ProductVariant $variant, array $valueIds): void
    {
        $values = [];
        $selectedGroups = [];
        foreach (array_unique($valueIds) as $valueId) {
            $value = $this->value($storeId, $productId, $valueId);
            if (!$value instanceof ProductOptionValue) {
                continue;
            }
            $groupId = $value->optionGroup()->id();
            if (isset($selectedGroups[$groupId])) {
                throw new InvalidArgumentException('Для каждой группы опций выберите не более одного значения.');
            }
            $selectedGroups[$groupId] = true;
            $values[] = $value;
        }

        $this->entityManager->createQueryBuilder()
            ->delete(ProductVariantOptionValue::class, 'link')
            ->where('link.variant = :variant')
            ->setParameter('variant', $variant)
            ->getQuery()
            ->execute();
        foreach ($values as $value) {
            $this->entityManager->persist(new ProductVariantOptionValue($this->id(), $variant, $value));
        }
    }

    private function id(): string
    {
        return Uuid::v7()->toRfc4122();
    }

    private function shortCode(string $id): string
    {
        return substr(str_replace('-', '', $id), -12);
    }
}
