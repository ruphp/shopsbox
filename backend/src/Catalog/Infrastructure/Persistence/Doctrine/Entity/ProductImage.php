<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Entity;

use App\Catalog\Infrastructure\Persistence\Doctrine\Repository\DoctrineProductImageRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DoctrineProductImageRepository::class)]
#[ORM\Table(name: 'product_images')]
#[ORM\Index(name: 'idx_product_images_product', columns: ['product_id'])]
class ProductImage
{
    #[ORM\Id]
    #[ORM\Column(type: 'guid')]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Product $product;

    #[ORM\Column(name: 'storage_key', length: 500)]
    private string $key;

    #[ORM\Column(length: 700)]
    private string $publicUrl;

    #[ORM\Column(length: 120)]
    private string $mimeType;

    #[ORM\Column]
    private int $size;

    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    public function __construct(
        string $id,
        Product $product,
        string $key,
        string $publicUrl,
        string $mimeType,
        int $size,
    ) {
        $this->id = $id;
        $this->product = $product;
        $this->key = $key;
        $this->publicUrl = $publicUrl;
        $this->mimeType = $mimeType;
        $this->size = $size;
        $this->createdAt = new DateTimeImmutable();
    }

    public function id(): string
    {
        return $this->id;
    }

    public function product(): Product
    {
        return $this->product;
    }

    public function key(): string
    {
        return $this->key;
    }

    public function publicUrl(): string
    {
        return $this->publicUrl;
    }

    public function mimeType(): string
    {
        return $this->mimeType;
    }

    public function size(): int
    {
        return $this->size;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
