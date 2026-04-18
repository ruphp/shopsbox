<?php

declare(strict_types=1);

namespace App\Catalog\Application\UseCase;

use App\Catalog\Application\Contracts\CategoryRepository;
use App\Catalog\Application\Contracts\EntityFlusher;
use App\Catalog\Application\Contracts\ProductRepository;
use App\Catalog\Application\Contracts\UuidGenerator;
use App\Catalog\Application\Dto\CreateProductInput;
use App\Catalog\Application\Dto\ProductView;
use App\Catalog\Application\Exception\InvalidProductInput;
use App\Catalog\Domain\ProductStatus;

final readonly class CreateProductUseCase
{
    public function __construct(
        private ProductRepository $productRepository,
        private CategoryRepository $categoryRepository,
        private EntityFlusher $entityFlusher,
        private UuidGenerator $uuidGenerator,
    ) {
    }

    public function execute(CreateProductInput $input): ProductView
    {
        $status = ProductStatus::tryFrom($input->status);
        if (!$status instanceof ProductStatus) {
            throw InvalidProductInput::forField('status', 'Unsupported product status.');
        }

        $this->validateTenantId($input->tenantId);
        $this->validateStoreId($input->storeId);
        $this->validateName($input->name);
        $this->validateSlug($input->slug);
        $this->validateCategory($input->storeId, $input->categoryId);

        if ($this->productRepository->existsByStoreAndSlug($input->storeId, $input->slug)) {
            throw InvalidProductInput::forField('slug', 'Product slug is already used in this store.');
        }

        $product = $this->productRepository->persist(
            $this->uuidGenerator->generate(),
            $input->tenantId,
            $input->storeId,
            $input->categoryId,
            $input->name,
            $input->slug,
            $this->normalizeDescription($input->description),
            $status,
        );
        $this->entityFlusher->flush();

        return $product;
    }

    private function validateTenantId(string $tenantId): void
    {
        if (!$this->isUuid($tenantId)) {
            throw InvalidProductInput::forField('tenant_id', 'Tenant id must be a valid UUID.');
        }
    }

    private function validateStoreId(string $storeId): void
    {
        if (!$this->isUuid($storeId)) {
            throw InvalidProductInput::forField('store_id', 'Store id must be a valid UUID.');
        }
    }

    private function validateName(string $name): void
    {
        if (trim($name) === '' || strlen($name) > 180) {
            throw InvalidProductInput::forField('name', 'Product name must be from 1 to 180 characters.');
        }
    }

    private function validateSlug(string $slug): void
    {
        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) || strlen($slug) > 140) {
            throw InvalidProductInput::forField('slug', 'Product slug must contain lowercase latin letters, digits and hyphens.');
        }
    }

    private function validateCategory(string $storeId, ?string $categoryId): void
    {
        if ($categoryId === null || $categoryId === '') {
            return;
        }

        if (!$this->isUuid($categoryId)) {
            throw InvalidProductInput::forField('category_id', 'Category id must be a valid UUID.');
        }

        if (!$this->categoryRepository->existsByStore($storeId, $categoryId)) {
            throw InvalidProductInput::forField('category_id', 'Category does not belong to this store.');
        }
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
