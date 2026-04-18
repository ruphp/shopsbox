<?php

declare(strict_types=1);

namespace App\ResourceUsage\Infrastructure\Persistence\Doctrine\Entity;

use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\Store;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'store_usage_limits')]
#[ORM\UniqueConstraint(name: 'uniq_store_usage_limits_resource', columns: ['store_id', 'resource_type'])]
#[ORM\Index(name: 'idx_store_usage_limits_store', columns: ['store_id'])]
#[ORM\Index(name: 'idx_store_usage_limits_plan', columns: ['plan_code'])]
class StoreUsageLimit
{
    #[ORM\Id]
    #[ORM\Column(type: 'guid')]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Store::class)]
    #[ORM\JoinColumn(name: 'store_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Store $store;

    #[ORM\Column(length: 64)]
    private string $planCode;

    #[ORM\Column(length: 80)]
    private string $resourceType;

    #[ORM\Column(type: 'decimal', precision: 20, scale: 4, nullable: true)]
    private ?string $softLimit;

    #[ORM\Column(type: 'decimal', precision: 20, scale: 4, nullable: true)]
    private ?string $hardLimit;

    #[ORM\Column(length: 32)]
    private string $unit;

    #[ORM\Column(length: 32)]
    private string $resetPeriod;

    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    #[ORM\Column]
    private DateTimeImmutable $updatedAt;

    public function __construct(
        string $id,
        Store $store,
        string $planCode,
        string $resourceType,
        ?string $softLimit,
        ?string $hardLimit,
        string $unit,
        string $resetPeriod,
    ) {
        $now = new DateTimeImmutable();

        $this->id = $id;
        $this->store = $store;
        $this->planCode = $planCode;
        $this->resourceType = $resourceType;
        $this->softLimit = $softLimit;
        $this->hardLimit = $hardLimit;
        $this->unit = $unit;
        $this->resetPeriod = $resetPeriod;
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function store(): Store
    {
        return $this->store;
    }
}
