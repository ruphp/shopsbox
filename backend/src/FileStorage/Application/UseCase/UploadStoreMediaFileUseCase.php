<?php

declare(strict_types=1);

namespace App\FileStorage\Application\UseCase;

use App\Catalog\Application\Contracts\EntityFlusher;
use App\Catalog\Application\Contracts\UuidGenerator;
use App\FileStorage\Application\Contracts\FileStorage;
use App\FileStorage\Application\Contracts\StoreMediaLibraryRepository;
use App\FileStorage\Application\Dto\StoreMediaFileView;
use App\Tenant\Application\UseCase\ShowStoreSettingsUseCase;
use InvalidArgumentException;
use RuntimeException;

final readonly class UploadStoreMediaFileUseCase
{
    private const MIME_EXTENSIONS = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];

    public function __construct(
        private ShowStoreSettingsUseCase $showStoreSettings,
        private StoreFileUsagePolicy $fileUsagePolicy,
        private StoreMediaLibraryRepository $repository,
        private FileStorage $fileStorage,
        private UuidGenerator $uuidGenerator,
        private EntityFlusher $flusher,
    ) {}

    public function execute(string $ownerEmail, string $originalName, string $mimeType, int $size, string $contents): StoreMediaFileView
    {
        if ($size <= 0 || $contents === '' || $size > 5_242_880) {
            throw new InvalidArgumentException('Размер изображения должен быть от 1 байта до 5 МБ.');
        }
        if (!isset(self::MIME_EXTENSIONS[$mimeType])) {
            throw new InvalidArgumentException('Поддерживаются JPG, PNG, WebP и GIF.');
        }
        $settings = $this->showStoreSettings->execute($ownerEmail);
        try { $this->fileUsagePolicy->assertCanAdd($settings->storeId, $size); }
        catch (RuntimeException) { throw new InvalidArgumentException('Исчерпан лимит медиатеки.'); }

        $id = $this->uuidGenerator->generate();
        $key = sprintf('stores/%s/media/%s.%s', $settings->storeId, $id, self::MIME_EXTENSIONS[$mimeType]);
        $stored = $this->fileStorage->write($key, $contents, $mimeType);
        $file = $this->repository->persist($id, $settings->storeId, $stored->key, $stored->publicUrl, $originalName, $mimeType, $size);
        $this->flusher->flush();
        return $file;
    }
}
