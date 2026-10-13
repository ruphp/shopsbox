<?php

declare(strict_types=1);

namespace App\FileStorage\Infrastructure\Persistence\Doctrine\Repository;

use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\ProductImage;
use App\FileStorage\Application\Contracts\StoreMediaLibraryRepository;
use App\FileStorage\Application\Dto\StoreMediaFileView;
use App\FileStorage\Infrastructure\Persistence\Doctrine\Entity\MediaFile;
use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\Store;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineStoreMediaLibraryRepository implements StoreMediaLibraryRepository
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function listByStore(string $storeId, ?string $mediaType = null): array
    {
        $files = $this->entityManager->createQueryBuilder()->select('file')->from(MediaFile::class, 'file')
            ->where('IDENTITY(file.store) = :storeId')->andWhere('file.deletedAt IS NULL')
            ->setParameter('storeId', $storeId)->orderBy('file.createdAt', 'DESC')->getQuery()->getResult();
        $views = array_map(fn (MediaFile $file): StoreMediaFileView => $this->map($file), $files);

        return $mediaType === null || $mediaType === '' ? $views : array_values(array_filter(
            $views,
            static fn (StoreMediaFileView $file): bool => $file->mediaType === $mediaType,
        ));
    }

    public function totalSizeByStore(string $storeId): int
    {
        return (int) $this->entityManager->createQueryBuilder()->select('COALESCE(SUM(file.size), 0)')
            ->from(MediaFile::class, 'file')->where('IDENTITY(file.store) = :storeId')->andWhere('file.deletedAt IS NULL')
            ->setParameter('storeId', $storeId)->getQuery()->getSingleScalarResult();
    }

    public function countByStore(string $storeId): int
    {
        return (int) $this->entityManager->createQueryBuilder()->select('COUNT(file.id)')
            ->from(MediaFile::class, 'file')->where('IDENTITY(file.store) = :storeId')->andWhere('file.deletedAt IS NULL')
            ->setParameter('storeId', $storeId)->getQuery()->getSingleScalarResult();
    }

    public function persist(string $id, string $storeId, string $key, string $publicUrl, string $originalName, string $mimeType, int $size): StoreMediaFileView
    {
        $file = new MediaFile($id, $this->entityManager->getReference(Store::class, $storeId), $key, $publicUrl, $originalName, $mimeType, $size);
        $this->entityManager->persist($file);
        return $this->map($file);
    }

    public function findByStore(string $storeId, string $fileId): ?StoreMediaFileView
    {
        $file = $this->findEntity($storeId, $fileId);
        return $file instanceof MediaFile ? $this->map($file) : null;
    }

    public function deleteIfUnused(string $storeId, string $fileId): ?string
    {
        $file = $this->findEntity($storeId, $fileId);
        if (!$file instanceof MediaFile || $this->usageLabels($file) !== []) {
            return null;
        }
        $file->delete();
        return $file->key();
    }

    private function findEntity(string $storeId, string $fileId): ?MediaFile
    {
        $file = $this->entityManager->createQueryBuilder()->select('file')->from(MediaFile::class, 'file')
            ->where('file.id = :fileId')->andWhere('IDENTITY(file.store) = :storeId')->andWhere('file.deletedAt IS NULL')
            ->setParameter('fileId', $fileId)->setParameter('storeId', $storeId)->getQuery()->getOneOrNullResult();
        return $file instanceof MediaFile ? $file : null;
    }

    private function map(MediaFile $file): StoreMediaFileView
    {
        $usage = $this->usageLabels($file);
        return new StoreMediaFileView(
            $file->id(), $file->key(), $file->publicUrl(), $file->mimeType(),
            str_starts_with($file->mimeType(), 'image/') ? 'image' : (str_starts_with($file->mimeType(), 'video/') ? 'video' : 'document'),
            $file->size(), $usage === [] ? 'Не используется' : implode(', ', $usage), $file->createdAt()->format('Y-m-d H:i'),
            $file->originalName(), count($usage),
        );
    }

    /** @return list<string> */
    private function usageLabels(MediaFile $file): array
    {
        $names = $this->entityManager->createQueryBuilder()->select('DISTINCT product.name')
            ->from(ProductImage::class, 'image')->join('image.product', 'product')
            ->where('IDENTITY(image.mediaFile) = :fileId')->andWhere('image.deletedAt IS NULL')
            ->setParameter('fileId', $file->id())->getQuery()->getSingleColumnResult();
        $usage = array_map(static fn (string $name): string => 'Товар: '.$name, $names);
        $theme = $file->store()->themeSettings();
        $usage = [...$usage, ...$this->themeUsageLabels($file->id(), $theme)];
        $draft = $theme['appearance_draft'] ?? null;
        if (is_array($draft)) {
            $usage = [...$usage, ...$this->themeUsageLabels($file->id(), $draft, 'Черновик: ')];
        }

        return array_values(array_unique($usage));
    }

    /**
     * @param array<string, mixed> $theme
     *
     * @return list<string>
     */
    private function themeUsageLabels(string $fileId, array $theme, string $prefix = ''): array
    {
        $usage = [];
        if (($theme['logo_image_id'] ?? null) === $fileId) { $usage[] = $prefix.'Логотип'; }
        if (($theme['hero_image_id'] ?? null) === $fileId) { $usage[] = $prefix.'Баннер'; }
        foreach ((array) ($theme['hero_slides'] ?? []) as $index => $slide) {
            if (is_array($slide) && ($slide['image_id'] ?? null) === $fileId) {
                $usage[] = $prefix.'Баннер: слайд '.($index + 1);
            }
        }
        foreach ((array) ($theme['category_images'] ?? []) as $categorySlug => $categoryImage) {
            if (is_array($categoryImage) && ($categoryImage['image_id'] ?? null) === $fileId) {
                $usage[] = $prefix.'Категория: '.(string) $categorySlug;
            }
        }
        if (($theme['content_image_id'] ?? null) === $fileId) { $usage[] = $prefix.'Текстовый блок'; }

        return $usage;
    }
}
