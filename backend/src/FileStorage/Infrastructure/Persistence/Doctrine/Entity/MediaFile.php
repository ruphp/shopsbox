<?php

declare(strict_types=1);

namespace App\FileStorage\Infrastructure\Persistence\Doctrine\Entity;

use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\Store;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'media_files')]
#[ORM\Index(name: 'idx_media_files_store', columns: ['store_id'])]
class MediaFile
{
    #[ORM\Id]
    #[ORM\Column(type: 'guid')]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Store::class)]
    #[ORM\JoinColumn(name: 'store_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Store $store;

    #[ORM\Column(name: 'storage_key', length: 500)]
    private string $key;

    #[ORM\Column(length: 700)]
    private string $publicUrl;

    #[ORM\Column(length: 255)]
    private string $originalName;

    #[ORM\Column(length: 120)]
    private string $mimeType;

    #[ORM\Column]
    private int $size;

    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $deletedAt = null;

    public function __construct(string $id, Store $store, string $key, string $publicUrl, string $originalName, string $mimeType, int $size)
    {
        $this->id = $id;
        $this->store = $store;
        $this->key = $key;
        $this->publicUrl = $publicUrl;
        $this->originalName = $originalName;
        $this->mimeType = $mimeType;
        $this->size = $size;
        $this->createdAt = new DateTimeImmutable();
    }

    public function id(): string { return $this->id; }
    public function store(): Store { return $this->store; }
    public function key(): string { return $this->key; }
    public function publicUrl(): string { return $this->publicUrl; }
    public function originalName(): string { return $this->originalName; }
    public function mimeType(): string { return $this->mimeType; }
    public function size(): int { return $this->size; }
    public function createdAt(): DateTimeImmutable { return $this->createdAt; }
    public function deletedAt(): ?DateTimeImmutable { return $this->deletedAt; }
    public function delete(): void { $this->deletedAt = new DateTimeImmutable(); }
}
