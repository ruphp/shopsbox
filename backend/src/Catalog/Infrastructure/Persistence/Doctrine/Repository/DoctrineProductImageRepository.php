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
        $this->entityManager->persist($image);

        return $this->map($image);
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
        );
    }
}
