<?php

declare(strict_types=1);

namespace App\Tenant\Infrastructure\Persistence\Doctrine\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'tenants')]
class Tenant
{
    #[ORM\Id]
    #[ORM\Column(type: 'guid')]
    private string $id;

    #[ORM\Column(length: 160)]
    private string $name;

    #[ORM\Column(length: 32)]
    private string $status;

    #[ORM\Column(length: 180)]
    private string $billingEmail;

    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    #[ORM\Column]
    private DateTimeImmutable $updatedAt;

    public function __construct(string $id, string $name, string $status, string $billingEmail)
    {
        $now = new DateTimeImmutable();

        $this->id = $id;
        $this->name = $name;
        $this->status = $status;
        $this->billingEmail = $billingEmail;
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function status(): string
    {
        return $this->status;
    }
}
