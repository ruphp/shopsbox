<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Persistence\Doctrine\Entity;

use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\Store;
use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\Tenant;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'product_option_groups')]
#[ORM\UniqueConstraint(name: 'uniq_product_option_groups_product_code', columns: ['product_id', 'code'])]
#[ORM\Index(name: 'idx_product_option_groups_tenant', columns: ['tenant_id'])]
#[ORM\Index(name: 'idx_product_option_groups_store', columns: ['store_id'])]
#[ORM\Index(name: 'idx_product_option_groups_product', columns: ['product_id'])]
class ProductOptionGroup
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

    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Product $product;

    #[ORM\Column(length: 80)]
    private string $code;

    #[ORM\Column(length: 120)]
    private string $name;

    #[ORM\Column]
    private int $position;

    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    public function __construct(string $id, Tenant $tenant, Store $store, Product $product, string $code, string $name, int $position)
    {
        $this->id = $id;
        $this->tenant = $tenant;
        $this->store = $store;
        $this->product = $product;
        $this->code = $code;
        $this->name = $name;
        $this->position = $position;
        $this->createdAt = new DateTimeImmutable();
    }
}
