<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Repository;

use App\Catalog\Application\Contracts\ProductImageRepository;
use App\Catalog\Application\Dto\ProductImageView;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\Product;
use App\Catalog\Infrastructure\Persistence\Doctrine\Entity\ProductImage;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;

final readonly class DoctrineProductImageRepository implements ProductImageRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function persist(
        string $id,
        string $productId,
        string $key,
        string $publicUrl,
        string $mimeType,
        int $size,
    ): ProductImageView {
        $product = $this->entityManager->getReference(Product::class, $productId);
        if (!$product instanceof Product) {
            throw new LogicException('Product reference must resolve to a Doctrine entity.');
        }

        $image = new ProductImage($id, $product, $key, $publicUrl, $mimeType, $size);
        $image->changePosition($this->nextPosition($productId));
        if ($image->position() === 10) {
            $image->makePrimary();
        }
        $this->entityManager->persist($image);

        return $this->map($image);
    }

    public function listByStoreAndProduct(string $storeId, string $productId): array
    {
        $images = $this->entityManager->createQueryBuilder()
            ->select('image')
            ->from(ProductImage::class, 'image')
            ->join('image.product', 'product')
            ->where('product.id = :productId')
            ->andWhere('IDENTITY(product.store) = :storeId')
            ->andWhere('image.deletedAt IS NULL')
            ->setParameter('productId', $productId)
            ->setParameter('storeId', $storeId)
            ->orderBy('image.primaryImage', 'DESC')
            ->addOrderBy('image.position', 'ASC')
            ->getQuery()
            ->getResult();

        return array_map(fn (ProductImage $image): ProductImageView => $this->map($image), $images);
    }

    public function setPrimary(string $storeId, string $productId, string $imageId): ?ProductImageView
    {
        $image = $this->findByStoreProductAndId($storeId, $productId, $imageId);
        if (!$image instanceof ProductImage) {
            return null;
        }

        foreach ($this->activeImages($storeId, $productId) as $candidate) {
            $candidate->makeSecondary();
        }

        $image->makePrimary();

        return $this->map($image);
    }

    public function changePosition(string $storeId, string $productId, string $imageId, int $position): ?ProductImageView
    {
        $image = $this->findByStoreProductAndId($storeId, $productId, $imageId);
        if (!$image instanceof ProductImage) {
            return null;
        }

        $image->changePosition($position);

        return $this->map($image);
    }

    public function delete(string $storeId, string $productId, string $imageId): bool
    {
        $image = $this->findByStoreProductAndId($storeId, $productId, $imageId);
        if (!$image instanceof ProductImage) {
            return false;
        }

        $wasPrimary = $image->primaryImage();
        $image->delete();

        if ($wasPrimary) {
            $next = $this->activeImages($storeId, $productId)[0] ?? null;
            if ($next instanceof ProductImage) {
                $next->makePrimary();
            }
        }

        return true;
    }

    private function map(ProductImage $image): ProductImageView
    {
        return new ProductImageView(
            $image->id(),
            $image->product()->id(),
            $image->key(),
            $image->publicUrl(),
            $image->mimeType(),
            $image->size(),
            $image->createdAt()->format(DATE_ATOM),
            $image->primaryImage(),
            $image->position(),
        );
    }

    private function nextPosition(string $productId): int
    {
        return ((int) $this->entityManager->createQueryBuilder()
            ->select('COUNT(image.id)')
            ->from(ProductImage::class, 'image')
            ->where('IDENTITY(image.product) = :productId')
            ->andWhere('image.deletedAt IS NULL')
            ->setParameter('productId', $productId)
            ->getQuery()
            ->getSingleScalarResult() + 1) * 10;
    }

    private function findByStoreProductAndId(string $storeId, string $productId, string $imageId): ?ProductImage
    {
        $image = $this->entityManager->createQueryBuilder()
            ->select('image')
            ->from(ProductImage::class, 'image')
            ->join('image.product', 'product')
            ->where('image.id = :imageId')
            ->andWhere('product.id = :productId')
            ->andWhere('IDENTITY(product.store) = :storeId')
            ->andWhere('image.deletedAt IS NULL')
            ->setParameter('imageId', $imageId)
            ->setParameter('productId', $productId)
            ->setParameter('storeId', $storeId)
            ->getQuery()
            ->getOneOrNullResult();

        return $image instanceof ProductImage ? $image : null;
    }

    /**
     * @return list<ProductImage>
     */
    private function activeImages(string $storeId, string $productId): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('image')
            ->from(ProductImage::class, 'image')
            ->join('image.product', 'product')
            ->where('product.id = :productId')
            ->andWhere('IDENTITY(product.store) = :storeId')
            ->andWhere('image.deletedAt IS NULL')
            ->setParameter('productId', $productId)
            ->setParameter('storeId', $storeId)
            ->orderBy('image.position', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
