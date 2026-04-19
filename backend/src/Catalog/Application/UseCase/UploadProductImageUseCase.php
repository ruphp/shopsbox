<?php

declare(strict_types=1);

namespace App\Catalog\Application\UseCase;

use App\Catalog\Application\Contracts\EntityFlusher;
use App\Catalog\Application\Contracts\ProductImageRepository;
use App\Catalog\Application\Contracts\ProductRepository;
use App\Catalog\Application\Contracts\UuidGenerator;
use App\Catalog\Application\Dto\ProductImageView;
use App\Catalog\Application\Dto\ProductView;
use App\Catalog\Application\Dto\UploadProductImageInput;
use App\Catalog\Application\Exception\InvalidProductImageInput;
use App\Catalog\Application\Exception\ProductNotFound;
use App\FileStorage\Application\Contracts\FileStorage;

final readonly class UploadProductImageUseCase
{
    private const MAX_SIZE = 5_242_880;

    private const MIME_EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    public function __construct(
        private ProductRepository $productRepository,
        private ProductImageRepository $productImageRepository,
        private FileStorage $fileStorage,
        private EntityFlusher $entityFlusher,
        private UuidGenerator $uuidGenerator,
    ) {
    }

    public function execute(UploadProductImageInput $input): ProductImageView
    {
        $this->validateInput($input);

        $product = $this->productRepository->findByStore($input->storeId, $input->productId);
        if (!$product instanceof ProductView) {
            throw ProductNotFound::byId($input->productId);
        }

        $imageId = $this->uuidGenerator->generate();
        $key = sprintf(
            'catalog/products/%s/images/%s.%s',
            $input->productId,
            $imageId,
            self::MIME_EXTENSIONS[$input->mimeType],
        );

        $storedFile = $this->fileStorage->write($key, $input->contents, $input->mimeType);
        $image = $this->productImageRepository->persist(
            $imageId,
            $input->productId,
            $storedFile->key,
            $storedFile->publicUrl,
            $input->mimeType,
            $input->size,
        );

        $this->entityFlusher->flush();

        return $image;
    }

    private function validateInput(UploadProductImageInput $input): void
    {
        if (!$this->isUuid($input->storeId)) {
            throw InvalidProductImageInput::forField('store_id', 'Store id must be a valid UUID.');
        }

        if (!$this->isUuid($input->productId)) {
            throw InvalidProductImageInput::forField('product_id', 'Product id must be a valid UUID.');
        }

        if ($input->size <= 0 || $input->contents === '') {
            throw InvalidProductImageInput::forField('image', 'Image file must not be empty.');
        }

        if ($input->size > self::MAX_SIZE) {
            throw InvalidProductImageInput::forField('image', 'Image file is too large.');
        }

        if (!array_key_exists($input->mimeType, self::MIME_EXTENSIONS)) {
            throw InvalidProductImageInput::forField('mime_type', 'Unsupported image type.');
        }
    }

    private function isUuid(string $value): bool
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-7][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value) === 1;
    }
}
