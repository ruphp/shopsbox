<?php

declare(strict_types=1);

namespace App\Tests\Catalog\Application\UseCase;

use App\Catalog\Application\Contracts\EntityFlusher;
use App\Catalog\Application\Contracts\ProductImageRepository;
use App\Catalog\Application\Contracts\ProductRepository;
use App\Catalog\Application\Contracts\UuidGenerator;
use App\Catalog\Application\Dto\ProductImageView;
use App\Catalog\Application\Dto\ProductView;
use App\Catalog\Application\Dto\UploadProductImageInput;
use App\Catalog\Application\Exception\InvalidProductImageInput;
use App\Catalog\Application\UseCase\UploadProductImageUseCase;
use App\Catalog\Domain\ProductStatus;
use App\FileStorage\Application\Contracts\FileStorage;
use App\FileStorage\Application\Dto\StoredFile;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class UploadProductImageUseCaseTest extends TestCase
{
    public function testItStoresFileAndPersistsMetadata(): void
    {
        $productRepository = new FakeUploadImageProductRepository($this->product());
        $imageRepository = new FakeProductImageRepository();
        $fileStorage = new FakeFileStorage();
        $flusher = new SpyUploadImageEntityFlusher();
        $useCase = new UploadProductImageUseCase(
            $productRepository,
            $imageRepository,
            $fileStorage,
            $flusher,
            new StubUploadImageUuidGenerator('77777777-7777-4777-8777-777777777777'),
        );

        $result = $useCase->execute($this->validInput());

        self::assertSame('77777777-7777-4777-8777-777777777777', $result->id);
        self::assertSame('33333333-3333-4333-8333-333333333333', $result->productId);
        self::assertSame('catalog/products/33333333-3333-4333-8333-333333333333/images/77777777-7777-4777-8777-777777777777.png', $result->key);
        self::assertSame('http://files.local/catalog/products/33333333-3333-4333-8333-333333333333/images/77777777-7777-4777-8777-777777777777.png', $result->publicUrl);
        self::assertSame('image/png', $fileStorage->written[0]['contentType']);
        self::assertSame('image-binary', $fileStorage->written[0]['contents']);
        self::assertCount(1, $imageRepository->saved);
        self::assertTrue($flusher->flushed);
    }

    public function testItRejectsUnsupportedMimeType(): void
    {
        $useCase = new UploadProductImageUseCase(
            new FakeUploadImageProductRepository($this->product()),
            new FakeProductImageRepository(),
            new FakeFileStorage(),
            new SpyUploadImageEntityFlusher(),
            new StubUploadImageUuidGenerator('77777777-7777-4777-8777-777777777777'),
        );

        $this->expectException(InvalidProductImageInput::class);

        $useCase->execute(new UploadProductImageInput(
            '22222222-2222-4222-8222-222222222222',
            '33333333-3333-4333-8333-333333333333',
            'demo.txt',
            'text/plain',
            12,
            'text',
        ));
    }

    private function validInput(): UploadProductImageInput
    {
        return new UploadProductImageInput(
            '22222222-2222-4222-8222-222222222222',
            '33333333-3333-4333-8333-333333333333',
            'demo.png',
            'image/png',
            12,
            'image-binary',
        );
    }

    private function product(): ProductView
    {
        return new ProductView(
            '33333333-3333-4333-8333-333333333333',
            '11111111-1111-4111-8111-111111111111',
            '22222222-2222-4222-8222-222222222222',
            null,
            'Demo product',
            'demo-product',
            null,
            'active',
            '2026-04-19T12:00:00+00:00',
            '2026-04-19T12:00:00+00:00',
        );
    }
}

final class FakeUploadImageProductRepository implements ProductRepository
{
    public function __construct(private readonly ProductView $product)
    {
    }

    public function listByStore(string $storeId): array
    {
        return [$this->product];
    }

    public function findByStore(string $storeId, string $productId): ?ProductView
    {
        if ($this->product->storeId === $storeId && $this->product->id === $productId) {
            return $this->product;
        }

        return null;
    }

    public function existsByStoreAndSlug(string $storeId, string $slug, ?string $exceptProductId = null): bool
    {
        return false;
    }

    public function persist(
        string $id,
        string $tenantId,
        string $storeId,
        ?string $categoryId,
        string $name,
        string $slug,
        ?string $description,
        ProductStatus $status,
    ): ProductView {
        throw new RuntimeException('Not used in this test.');
    }

    public function update(
        string $storeId,
        string $productId,
        ?string $categoryId,
        string $name,
        string $slug,
        ?string $description,
    ): ?ProductView {
        throw new RuntimeException('Not used in this test.');
    }

    public function changeStatus(string $storeId, string $productId, ProductStatus $status): ?ProductView
    {
        throw new RuntimeException('Not used in this test.');
    }
}

final class FakeProductImageRepository implements ProductImageRepository
{
    /**
     * @var list<array{id: string, productId: string, key: string, publicUrl: string, mimeType: string, size: int}>
     */
    public array $saved = [];

    public function persist(
        string $id,
        string $productId,
        string $key,
        string $publicUrl,
        string $mimeType,
        int $size,
    ): ProductImageView {
        $this->saved[] = [
            'id' => $id,
            'productId' => $productId,
            'key' => $key,
            'publicUrl' => $publicUrl,
            'mimeType' => $mimeType,
            'size' => $size,
        ];

        return new ProductImageView(
            $id,
            $productId,
            $key,
            $publicUrl,
            $mimeType,
            $size,
            '2026-04-19T12:00:00+00:00',
        );
    }
}

final class FakeFileStorage implements FileStorage
{
    /**
     * @var list<array{key: string, contents: string, contentType: string|null}>
     */
    public array $written = [];

    public function write(string $key, string $contents, ?string $contentType = null): StoredFile
    {
        $this->written[] = [
            'key' => $key,
            'contents' => $contents,
            'contentType' => $contentType,
        ];

        return new StoredFile($key, 'http://files.local/'.$key);
    }

    public function read(string $key): string
    {
        throw new RuntimeException('Not used in this test.');
    }

    public function delete(string $key): void
    {
        throw new RuntimeException('Not used in this test.');
    }
}

final class SpyUploadImageEntityFlusher implements EntityFlusher
{
    public bool $flushed = false;

    public function flush(): void
    {
        $this->flushed = true;
    }
}

final class StubUploadImageUuidGenerator implements UuidGenerator
{
    public function __construct(private readonly string $uuid)
    {
    }

    public function generate(): string
    {
        return $this->uuid;
    }
}
