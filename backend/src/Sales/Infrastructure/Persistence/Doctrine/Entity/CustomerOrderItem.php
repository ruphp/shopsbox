<?php

declare(strict_types=1);

namespace App\Sales\Infrastructure\Persistence\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'customer_order_items')]
#[ORM\Index(name: 'idx_customer_order_items_order_position', columns: ['order_id', 'position'])]
class CustomerOrderItem
{
    #[ORM\Id]
    #[ORM\Column(type: 'guid')]
    private string $id;

    #[ORM\ManyToOne(targetEntity: CustomerOrder::class, inversedBy: 'items')]
    #[ORM\JoinColumn(name: 'order_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private CustomerOrder $order;

    #[ORM\Column(type: 'guid')]
    private string $productId;

    #[ORM\Column(type: 'guid', nullable: true)]
    private ?string $variantId;

    #[ORM\Column(length: 180)]
    private string $productName;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $variantName;

    #[ORM\Column]
    private int $unitPriceMinor;

    #[ORM\Column]
    private int $quantity;

    #[ORM\Column]
    private int $lineTotalMinor;

    #[ORM\Column]
    private int $position;

    public function __construct(
        string $id,
        CustomerOrder $order,
        string $productId,
        ?string $variantId,
        string $productName,
        ?string $variantName,
        int $unitPriceMinor,
        int $quantity,
        int $position,
    ) {
        $this->id = $id;
        $this->order = $order;
        $this->productId = $productId;
        $this->variantId = $variantId;
        $this->productName = $productName;
        $this->variantName = $variantName;
        $this->unitPriceMinor = $unitPriceMinor;
        $this->quantity = $quantity;
        $this->lineTotalMinor = $unitPriceMinor * $quantity;
        $this->position = $position;
    }

    public function productName(): string { return $this->productName; }
    public function variantName(): ?string { return $this->variantName; }
    public function unitPriceMinor(): int { return $this->unitPriceMinor; }
    public function quantity(): int { return $this->quantity; }
    public function lineTotalMinor(): int { return $this->lineTotalMinor; }
}
