<?php

declare(strict_types=1);

namespace App\Tenant\Infrastructure\Persistence\Doctrine\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'stores')]
#[ORM\UniqueConstraint(name: 'uniq_stores_tenant_slug', columns: ['tenant_id', 'slug'])]
#[ORM\UniqueConstraint(name: 'uniq_stores_domain', columns: ['domain'])]
class Store
{
    #[ORM\Id]
    #[ORM\Column(type: 'guid')]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Tenant::class)]
    #[ORM\JoinColumn(name: 'tenant_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Tenant $tenant;

    #[ORM\Column(length: 160)]
    private string $name;

    #[ORM\Column(length: 120)]
    private string $slug;

    #[ORM\Column(length: 255)]
    private string $domain;

    #[ORM\Column(length: 32)]
    private string $status;

    #[ORM\Column(length: 3)]
    private string $defaultCurrency;

    #[ORM\Column(length: 64)]
    private string $timezone;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $publicDescription = null;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $contactEmail = null;

    #[ORM\Column(length: 40, nullable: true)]
    private ?string $contactPhone = null;

    #[ORM\Column(length: 160, nullable: true)]
    private ?string $contactCity = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $contactAddress = null;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $sellerLegalName = null;

    #[ORM\Column(length: 32, nullable: true)]
    private ?string $sellerInn = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $sellerLegalText = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $deliveryText = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $paymentText = null;

    /**
     * @var array<string, mixed>
     */
    #[ORM\Column(type: 'json')]
    private array $themeSettings = [];

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $publicationOwnerName = null;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $publicationEmail = null;

    #[ORM\Column(length: 40, nullable: true)]
    private ?string $publicationPhone = null;

    #[ORM\Column(length: 40, nullable: true)]
    private ?string $publicSubdomain = null;

    #[ORM\Column(length: 32)]
    private string $publicationStatus = 'draft';

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $publicationTermsAcceptedAt = null;

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
        string $name,
        string $slug,
        string $domain,
        string $status,
        string $defaultCurrency,
        string $timezone,
    ) {
        $now = new DateTimeImmutable();

        $this->id = $id;
        $this->tenant = $tenant;
        $this->name = $name;
        $this->slug = $slug;
        $this->domain = $domain;
        $this->status = $status;
        $this->defaultCurrency = $defaultCurrency;
        $this->timezone = $timezone;
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

    public function name(): string
    {
        return $this->name;
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function domain(): string
    {
        return $this->domain;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function defaultCurrency(): string
    {
        return $this->defaultCurrency;
    }

    public function timezone(): string
    {
        return $this->timezone;
    }

    public function publicDescription(): ?string
    {
        return $this->publicDescription;
    }

    public function contactEmail(): ?string
    {
        return $this->contactEmail;
    }

    public function contactPhone(): ?string
    {
        return $this->contactPhone;
    }

    public function contactCity(): ?string
    {
        return $this->contactCity;
    }

    public function contactAddress(): ?string
    {
        return $this->contactAddress;
    }

    public function sellerLegalName(): ?string
    {
        return $this->sellerLegalName;
    }

    public function sellerInn(): ?string
    {
        return $this->sellerInn;
    }

    public function sellerLegalText(): ?string
    {
        return $this->sellerLegalText;
    }

    public function deliveryText(): ?string
    {
        return $this->deliveryText;
    }

    public function paymentText(): ?string
    {
        return $this->paymentText;
    }

    /**
     * @return array<string, mixed>
     */
    public function themeSettings(): array
    {
        return $this->themeSettings;
    }

    public function publicationOwnerName(): ?string
    {
        return $this->publicationOwnerName;
    }

    public function publicationEmail(): ?string
    {
        return $this->publicationEmail;
    }

    public function publicationPhone(): ?string
    {
        return $this->publicationPhone;
    }

    public function publicSubdomain(): ?string
    {
        return $this->publicSubdomain;
    }

    public function publicationStatus(): string
    {
        return $this->publicationStatus;
    }

    public function publicationTermsAcceptedAt(): ?DateTimeImmutable
    {
        return $this->publicationTermsAcceptedAt;
    }

    public function publicationReviewReason(): ?string
    {
        return $this->publicationReviewReason;
    }

    public function updateSettings(
        string $name,
        ?string $publicDescription,
        ?string $contactEmail,
        ?string $contactPhone,
        string $defaultCurrency,
        string $timezone,
    ): void {
        $this->name = $name;
        $this->publicDescription = $publicDescription;
        $this->contactEmail = $contactEmail;
        $this->contactPhone = $contactPhone;
        $this->defaultCurrency = $defaultCurrency;
        $this->timezone = $timezone;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updatePublicBusinessData(
        ?string $contactCity,
        ?string $contactAddress,
        ?string $sellerLegalName,
        ?string $sellerInn,
        ?string $sellerLegalText,
        ?string $deliveryText,
        ?string $paymentText,
    ): void {
        $this->contactCity = $contactCity;
        $this->contactAddress = $contactAddress;
        $this->sellerLegalName = $sellerLegalName;
        $this->sellerInn = $sellerInn;
        $this->sellerLegalText = $sellerLegalText;
        $this->deliveryText = $deliveryText;
        $this->paymentText = $paymentText;
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * @param array<string, mixed> $themeSettings
     */
    public function updateThemeSettings(array $themeSettings): void
    {
        $this->themeSettings = $themeSettings;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updatePublicationRequest(
        string $ownerName,
        string $email,
        string $phone,
        string $publicSubdomain,
        bool $termsAccepted,
    ): void {
        $this->publicationOwnerName = $ownerName;
        $this->publicationEmail = $email;
        $this->publicationPhone = $phone;
        $this->publicSubdomain = $publicSubdomain;
        $this->publicationStatus = 'pending_review';
        $this->publicationReviewedBy = null;
        $this->publicationReviewedAt = null;
        $this->publicationReviewReason = null;
        $this->publicationTermsAcceptedAt = $termsAccepted ? new DateTimeImmutable() : $this->publicationTermsAcceptedAt;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function approvePublication(?string $reviewedBy): void
    {
        $this->publicationStatus = 'published';
        $this->publicationReviewedBy = $reviewedBy;
        $this->publicationReviewedAt = new DateTimeImmutable();
        $this->publicationReviewReason = null;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function rejectPublication(?string $reviewedBy, string $reason): void
    {
        $this->publicationStatus = 'rejected';
        $this->publicationReviewedBy = $reviewedBy;
        $this->publicationReviewedAt = new DateTimeImmutable();
        $this->publicationReviewReason = $reason;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function blockPublication(?string $reviewedBy, string $reason): void
    {
        $this->publicationStatus = 'blocked';
        $this->publicationReviewedBy = $reviewedBy;
        $this->publicationReviewedAt = new DateTimeImmutable();
        $this->publicationReviewReason = $reason;
        $this->updatedAt = new DateTimeImmutable();
    }
}
