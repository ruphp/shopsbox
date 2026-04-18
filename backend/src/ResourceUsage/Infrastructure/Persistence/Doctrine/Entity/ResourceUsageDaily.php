<?php

declare(strict_types=1);

namespace App\ResourceUsage\Infrastructure\Persistence\Doctrine\Entity;

use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\Store;
use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\Tenant;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'resource_usage_daily')]
#[ORM\Index(name: 'idx_resource_usage_daily_tenant_date', columns: ['tenant_id', 'usage_date'])]
#[ORM\Index(name: 'idx_resource_usage_daily_store_date', columns: ['store_id', 'usage_date'])]
#[ORM\Index(name: 'idx_resource_usage_daily_type_date', columns: ['resource_type', 'usage_date'])]
class ResourceUsageDaily
{
    #[ORM\Id]
    #[ORM\Column(type: 'guid')]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Tenant::class)]
    #[ORM\JoinColumn(name: 'tenant_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Tenant $tenant;

    #[ORM\ManyToOne(targetEntity: Store::class)]
    #[ORM\JoinColumn(name: 'store_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    private ?Store $store;

    #[ORM\Column(type: 'date_immutable')]
    private DateTimeImmutable $usageDate;

    #[ORM\Column(length: 80)]
    private string $resourceType;

    #[ORM\Column(type: 'decimal', precision: 20, scale: 4)]
    private string $quantity;

    #[ORM\Column(length: 32)]
    private string $unit;

    #[ORM\Column(length: 64)]
    private string $source;

    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    public function __construct(
        string $id,
        Tenant $tenant,
        ?Store $store,
        DateTimeImmutable $usageDate,
        string $resourceType,
        string $quantity,
        string $unit,
        string $source,
    ) {
        $this->id = $id;
        $this->tenant = $tenant;
        $this->store = $store;
        $this->usageDate = $usageDate;
        $this->resourceType = $resourceType;
        $this->quantity = $quantity;
        $this->unit = $unit;
        $this->source = $source;
        $this->createdAt = new DateTimeImmutable();
    }

    public function id(): string
    {
        return $this->id;
    }

    public function tenant(): Tenant
    {
        return $this->tenant;
    }

    public function store(): ?Store
    {
        return $this->store;
    }
}
