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
        $this->publicationTermsAcceptedAt = $termsAccepted ? new DateTimeImmutable() : $this->publicationTermsAcceptedAt;
        $this->updatedAt = new DateTimeImmutable();
    }
}
