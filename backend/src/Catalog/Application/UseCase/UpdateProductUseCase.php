<?php

declare(strict_types=1);

namespace App\Catalog\Application\UseCase;

use App\Catalog\Application\Contracts\CategoryRepository;
use App\Catalog\Application\Contracts\EntityFlusher;
use App\Catalog\Application\Contracts\ProductRepository;
use App\Catalog\Application\Dto\ProductView;
use App\Catalog\Application\Dto\UpdateProductInput;
use App\Catalog\Application\Exception\InvalidProductInput;
use App\Catalog\Application\Exception\ProductNotFound;

final readonly class UpdateProductUseCase
{
    public function __construct(
        private ProductRepository $productRepository,
        private CategoryRepository $categoryRepository,
        private EntityFlusher $entityFlusher,
    ) {
    }

    public function execute(UpdateProductInput $input): ProductView
    {
        if (!$this->isUuid($input->storeId)) {
            throw InvalidProductInput::forField('store_id', 'Store id must be a valid UUID.');
        }

        if (!$this->isUuid($input->productId)) {
            throw InvalidProductInput::forField('product_id', 'Product id must be a valid UUID.');
        }

        if (trim($input->name) === '' || strlen($input->name) > 180) {
            throw InvalidProductInput::forField('name', 'Product name must be from 1 to 180 characters.');
        }

        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $input->slug) || strlen($input->slug) > 140) {
            throw InvalidProductInput::forField('slug', 'Product slug must contain lowercase latin letters, digits and hyphens.');
        }

        if ($input->categoryId !== null && $input->categoryId !== '') {
            if (!$this->isUuid($input->categoryId)) {
                throw InvalidProductInput::forField('category_id', 'Category id must be a valid UUID.');
            }

            if (!$this->categoryRepository->existsByStore($input->storeId, $input->categoryId)) {
                throw InvalidProductInput::forField('category_id', 'Category does not belong to this store.');
            }
        }

        if ($this->productRepository->existsByStoreAndSlug($input->storeId, $input->slug, $input->productId)) {
            throw InvalidProductInput::forField('slug', 'Product slug is already used in this store.');
        }

        $product = $this->productRepository->update(
            $input->storeId,
            $input->productId,
            $input->categoryId === '' ? null : $input->categoryId,
            $input->name,
            $input->slug,
            $this->normalizeDescription($input->description),
        );
        if (!$product instanceof ProductView) {
            throw ProductNotFound::byId($input->productId);
        }

        $this->entityFlusher->flush();

        return $product;
    }

    private function normalizeDescription(?string $description): ?string
    {
        $description = trim((string) $description);

        return $description === '' ? null : $description;
    }

    private function isUuid(string $value): bool
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-7][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value) === 1;
    }
}
