<?php

declare(strict_types=1);

namespace App\FileStorage\Infrastructure\Persistence\Doctrine\Repository;

use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\ProductImage;
use App\FileStorage\Application\Contracts\StoreMediaLibraryRepository;
use App\FileStorage\Application\Dto\StoreMediaFileView;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineStoreMediaLibraryRepository implements StoreMediaLibraryRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function listByStore(string $storeId, ?string $mediaType = null): array
    {
        $query = $this->entityManager->createQueryBuilder()
            ->select('image')
            ->from(ProductImage::class, 'image')
            ->join('image.product', 'product')
            ->where('IDENTITY(product.store) = :storeId')
            ->andWhere('image.deletedAt IS NULL')
            ->setParameter('storeId', $storeId)
            ->orderBy('image.createdAt', 'DESC');

        $files = array_map(
            fn (ProductImage $image): StoreMediaFileView => $this->mapImage($image),
            $query->getQuery()->getResult(),
        );

        if ($mediaType === null || $mediaType === '') {
            return $files;
        }

        return array_values(array_filter(
            $files,
            static fn (StoreMediaFileView $file): bool => $file->mediaType === $mediaType,
        ));
    }

    public function totalSizeByStore(string $storeId): int
    {
        return (int) $this->entityManager->createQueryBuilder()
            ->select('COALESCE(SUM(image.size), 0)')
            ->from(ProductImage::class, 'image')
            ->join('image.product', 'product')
            ->where('IDENTITY(product.store) = :storeId')
            ->andWhere('image.deletedAt IS NULL')
            ->setParameter('storeId', $storeId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countByStore(string $storeId): int
    {
        return (int) $this->entityManager->createQueryBuilder()
            ->select('COUNT(image.id)')
            ->from(ProductImage::class, 'image')
            ->join('image.product', 'product')
            ->where('IDENTITY(product.store) = :storeId')
            ->andWhere('image.deletedAt IS NULL')
            ->setParameter('storeId', $storeId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function mapImage(ProductImage $image): StoreMediaFileView
    {
        return new StoreMediaFileView(
            $image->id(),
            $image->key(),
            $image->publicUrl(),
            $image->mimeType(),
            str_starts_with($image->mimeType(), 'image/') ? 'image' : 'other',
            $image->size(),
            sprintf('Товар: %s', $image->product()->name()),
            $image->createdAt()->format('Y-m-d H:i'),
        );
    }
}
