<?php

declare(strict_types=1);

namespace App\Sales\Infrastructure\Persistence\Doctrine\Entity;

use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\Store;
use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\Tenant;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'customer_orders')]
#[ORM\UniqueConstraint(name: 'uniq_customer_orders_store_number', columns: ['store_id', 'number'])]
#[ORM\Index(name: 'idx_customer_orders_tenant_created', columns: ['tenant_id', 'created_at'])]
#[ORM\Index(name: 'idx_customer_orders_store_created', columns: ['store_id', 'created_at'])]
class CustomerOrder
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

    #[ORM\Column(length: 32)]
    private string $number;

    #[ORM\Column(length: 32)]
    private string $status = 'new';

    #[ORM\Column(length: 120)]
    private string $customerName;

    #[ORM\Column(length: 180)]
    private string $customerEmail;

    #[ORM\Column(length: 40)]
    private string $customerPhone;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $comment;

    #[ORM\Column]
    private int $totalMinor;

    #[ORM\Column(length: 3)]
    private string $currency;

    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    /** @var Collection<int, CustomerOrderItem> */
    #[ORM\OneToMany(mappedBy: 'order', targetEntity: CustomerOrderItem::class, cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $items;

    public function __construct(
        string $id,
        Tenant $tenant,
        Store $store,
        string $number,
        string $customerName,
        string $customerEmail,
        string $customerPhone,
        ?string $comment,
        int $totalMinor,
        string $currency,
    ) {
        $this->id = $id;
        $this->tenant = $tenant;
        $this->store = $store;
        $this->number = $number;
        $this->customerName = $customerName;
        $this->customerEmail = $customerEmail;
        $this->customerPhone = $customerPhone;
        $this->comment = $comment;
        $this->totalMinor = $totalMinor;
        $this->currency = $currency;
        $this->createdAt = new DateTimeImmutable();
        $this->items = new ArrayCollection();
    }

    public function id(): string { return $this->id; }
    public function number(): string { return $this->number; }
    public function status(): string { return $this->status; }
    public function customerName(): string { return $this->customerName; }
    public function customerEmail(): string { return $this->customerEmail; }
    public function customerPhone(): string { return $this->customerPhone; }
    public function comment(): ?string { return $this->comment; }
    public function totalMinor(): int { return $this->totalMinor; }
    public function currency(): string { return $this->currency; }
    public function createdAt(): DateTimeImmutable { return $this->createdAt; }

    /** @return list<CustomerOrderItem> */
    public function items(): array { return $this->items->toArray(); }

    public function addItem(CustomerOrderItem $item): void
    {
        $this->items->add($item);
    }
}
