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
}
