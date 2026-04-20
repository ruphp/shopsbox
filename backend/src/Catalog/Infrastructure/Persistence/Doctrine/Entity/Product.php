<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Entity;

use App\Catalog\Domain\ProductStatus;
use App\Catalog\Domain\ProductPublicationStatus;
use App\Catalog\Infrastructure\Persistence\Doctrine\Repository\DoctrineProductRepository;
use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\Store;
use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\Tenant;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DoctrineProductRepository::class)]
#[ORM\Table(name: 'products')]
#[ORM\UniqueConstraint(name: 'uniq_products_store_slug', columns: ['store_id', 'slug'])]
#[ORM\Index(name: 'idx_products_tenant', columns: ['tenant_id'])]
#[ORM\Index(name: 'idx_products_store', columns: ['store_id'])]
#[ORM\Index(name: 'idx_products_category', columns: ['category_id'])]
#[ORM\Index(name: 'idx_products_status', columns: ['status'])]
class Product
{
    #[ORM\Id]
    #[ORM\Column(type: 'guid')]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Tenant::class)]
    #[ORM\JoinColumn(name: 'tenant_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Tenant $tenant;

    #[ORM\ManyToOne(targetEntity: Store::class)]
    #[ORM\JoinColumn(name: 'store_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Store $store;

    #[ORM\ManyToOne(targetEntity: Category::class)]
    #[ORM\JoinColumn(name: 'category_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Category $category;

    #[ORM\Column(length: 180)]
    private string $name;

    #[ORM\Column(length: 140)]
    private string $slug;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description;

    #[ORM\Column(length: 32, enumType: ProductStatus::class)]
    private ProductStatus $status;

    #[ORM\Column(length: 32, enumType: ProductPublicationStatus::class)]
    private ProductPublicationStatus $publicationStatus = ProductPublicationStatus::DRAFT;

    #[ORM\Column(length: 36, nullable: true)]
    private ?string $publicationSubmittedBy = null;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $publicationSubmittedAt = null;

    #[ORM\Column(length: 36, nullable: true)]
    private ?string $publicationReviewedBy = null;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $publicationReviewedAt = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $publicationReviewReason = null;

    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    #[ORM\Column]
    private DateTimeImmutable $updatedAt;

    public function __construct(
        string $id,
        Tenant $tenant,
        Store $store,
        ?Category $category,
        string $name,
        string $slug,
        ?string $description,
        ProductStatus $status,
    ) {
        $now = new DateTimeImmutable();

        $this->id = $id;
        $this->tenant = $tenant;
        $this->store = $store;
        $this->category = $category;
        $this->name = $name;
        $this->slug = $slug;
        $this->description = $description;
        $this->status = $status;
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function tenant(): Tenant
    {
        return $this->tenant;
    }

    public function store(): Store
    {
        return $this->store;
    }

    public function category(): ?Category
    {
        return $this->category;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function status(): ProductStatus
    {
        return $this->status;
    }

    public function publicationStatus(): ProductPublicationStatus
    {
        return $this->publicationStatus;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function updateDetails(?Category $category, string $name, string $slug, ?string $description): void
    {
        $this->category = $category;
        $this->name = $name;
        $this->slug = $slug;
        $this->description = $description;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function changeStatus(ProductStatus $status): void
    {
        $this->status = $status;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function submitForPublicationReview(?string $submittedBy): void
    {
        $this->publicationStatus = ProductPublicationStatus::PENDING_REVIEW;
        $this->publicationSubmittedBy = $submittedBy;
        $this->publicationSubmittedAt = new DateTimeImmutable();
        $this->publicationReviewedBy = null;
        $this->publicationReviewedAt = null;
        $this->publicationReviewReason = null;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function approvePublication(?string $reviewedBy): void
    {
        $this->publicationStatus = ProductPublicationStatus::PUBLISHED;
        $this->publicationReviewedBy = $reviewedBy;
        $this->publicationReviewedAt = new DateTimeImmutable();
        $this->publicationReviewReason = null;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function rejectPublication(?string $reviewedBy, string $reason): void
    {
        $this->publicationStatus = ProductPublicationStatus::REJECTED;
        $this->publicationReviewedBy = $reviewedBy;
        $this->publicationReviewedAt = new DateTimeImmutable();
        $this->publicationReviewReason = $reason;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function blockPublication(?string $reviewedBy, string $reason): void
    {
        $this->publicationStatus = ProductPublicationStatus::BLOCKED;
        $this->publicationReviewedBy = $reviewedBy;
        $this->publicationReviewedAt = new DateTimeImmutable();
        $this->publicationReviewReason = $reason;
        $this->updatedAt = new DateTimeImmutable();
    }
}
