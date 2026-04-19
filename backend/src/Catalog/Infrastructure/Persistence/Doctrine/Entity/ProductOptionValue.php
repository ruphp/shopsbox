<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Entity;

use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\Store;
use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\Tenant;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'product_option_values')]
#[ORM\UniqueConstraint(name: 'uniq_product_option_values_group_code', columns: ['option_group_id', 'code'])]
#[ORM\Index(name: 'idx_product_option_values_tenant', columns: ['tenant_id'])]
#[ORM\Index(name: 'idx_product_option_values_store', columns: ['store_id'])]
#[ORM\Index(name: 'idx_product_option_values_group', columns: ['option_group_id'])]
class ProductOptionValue
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

    #[ORM\ManyToOne(targetEntity: ProductOptionGroup::class)]
    #[ORM\JoinColumn(name: 'option_group_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ProductOptionGroup $optionGroup;

    #[ORM\Column(length: 80)]
    private string $code;

    #[ORM\Column(length: 120)]
    private string $value;

    #[ORM\Column]
    private int $position;

    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    public function __construct(string $id, Tenant $tenant, Store $store, ProductOptionGroup $optionGroup, string $code, string $value, int $position)
    {
        $this->id = $id;
        $this->tenant = $tenant;
        $this->store = $store;
        $this->optionGroup = $optionGroup;
        $this->code = $code;
        $this->value = $value;
        $this->position = $position;
        $this->createdAt = new DateTimeImmutable();
    }
}
