<?php

declare(strict_types=1);

namespace App\Catalog\Application\UseCase;

use App\Catalog\Application\Contracts\EntityFlusher;
use App\Catalog\Application\Contracts\ProductRepository;
use App\Catalog\Application\Dto\ChangeProductStatusInput;
use App\Catalog\Application\Dto\ProductView;
use App\Catalog\Application\Exception\InvalidProductInput;
use App\Catalog\Application\Exception\InvalidProductStatusTransition;
use App\Catalog\Application\Exception\ProductNotFound;
use App\Catalog\Domain\ProductStatus;

final readonly class ChangeProductStatusUseCase
{
    public function __construct(
        private ProductRepository $productRepository,
        private EntityFlusher $entityFlusher,
    ) {
    }

    public function execute(ChangeProductStatusInput $input): ProductView
    {
        if (!$this->isUuid($input->storeId)) {
            throw InvalidProductInput::forField('store_id', 'Store id must be a valid UUID.');
        }

        if (!$this->isUuid($input->productId)) {
            throw InvalidProductInput::forField('product_id', 'Product id must be a valid UUID.');
        }

        $targetStatus = ProductStatus::tryFrom($input->status);
        if (!$targetStatus instanceof ProductStatus) {
            throw InvalidProductInput::forField('status', 'Unsupported product status.');
        }

        $product = $this->productRepository->findByStore($input->storeId, $input->productId);
        if (!$product instanceof ProductView) {
            throw ProductNotFound::byId($input->productId);
        }

        $currentStatus = ProductStatus::from($product->status);
        if (!$currentStatus->canTransitionTo($targetStatus)) {
            throw new InvalidProductStatusTransition(sprintf(
                'Product status cannot be changed from "%s" to "%s".',
                $currentStatus->value,
                $targetStatus->value,
            ));
        }

        $changedProduct = $this->productRepository->changeStatus($input->storeId, $input->productId, $targetStatus);
        if (!$changedProduct instanceof ProductView) {
            throw ProductNotFound::byId($input->productId);
        }

        $this->entityFlusher->flush();

        return $changedProduct;
    }

    private function isUuid(string $value): bool
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-7][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value) === 1;
    }
}
