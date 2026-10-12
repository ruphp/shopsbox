<?php

declare(strict_types=1);

namespace App\Catalog\Application\Contracts;

use App\Catalog\Application\Dto\ProductStructureView;

interface ProductStructureRepository
{
    public function view(string $storeId, string $productId): ProductStructureView;

    public function createAttribute(string $tenantId, string $storeId, string $productId, string $name, string $value): void;

    public function updateAttribute(string $storeId, string $productId, string $attributeId, string $name, string $value): bool;

    public function deleteAttribute(string $storeId, string $productId, string $attributeId): bool;

    public function createOptionGroup(string $tenantId, string $storeId, string $productId, string $name): void;

    public function updateOptionGroup(string $storeId, string $productId, string $groupId, string $name): bool;

    public function deleteOptionGroup(string $storeId, string $productId, string $groupId): bool;

    public function createOptionValue(string $tenantId, string $storeId, string $productId, string $groupId, string $value): bool;

    public function updateOptionValue(string $storeId, string $productId, string $valueId, string $value): bool;

    public function deleteOptionValue(string $storeId, string $productId, string $valueId): bool;

    /** @param list<string> $optionValueIds */
    public function createVariant(string $tenantId, string $storeId, string $productId, string $name, string $sku, ?string $priceAdjustment, bool $active, array $optionValueIds): void;

    /** @param list<string> $optionValueIds */
    public function updateVariant(string $storeId, string $productId, string $variantId, string $name, string $sku, ?string $priceAdjustment, bool $active, array $optionValueIds): bool;

    public function deleteVariant(string $storeId, string $productId, string $variantId): bool;
}
