<?php

declare(strict_types=1);

namespace App\Tenant\Infrastructure\Persistence\Doctrine\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
#[ORM\UniqueConstraint(name: 'uniq_users_email', columns: ['email'])]
class User implements PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'guid')]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Tenant::class)]
    #[ORM\JoinColumn(name: 'tenant_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    private ?Tenant $tenant;

    #[ORM\Column(length: 180)]
    private string $email;

    #[ORM\Column(length: 255)]
    private string $passwordHash = '';

    #[ORM\Column(length: 120)]
    private string $displayName;

    #[ORM\Column(length: 32)]
    private string $status;

    #[ORM\Column(length: 40, nullable: true)]
    private ?string $verifiedPhone = null;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $phoneVerifiedAt = null;

    #[ORM\Column(length: 45, nullable: true)]
    private ?string $phoneVerifiedIp = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $phoneVerifiedUserAgent = null;

    #[ORM\Column(length: 40, nullable: true)]
    private ?string $phoneVerificationProvider = null;

    #[ORM\Column]
    private bool $demo;

    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    #[ORM\Column]
    private DateTimeImmutable $updatedAt;

    public function __construct(
        string $id,
        ?Tenant $tenant,
        string $email,
        string $displayName,
        string $status,
        bool $demo,
    ) {
        $now = new DateTimeImmutable();

        $this->id = $id;
        $this->tenant = $tenant;
        $this->email = $email;
        $this->displayName = $displayName;
        $this->status = $status;
        $this->demo = $demo;
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function displayName(): string
    {
        return $this->displayName;
    }

    public function getPassword(): ?string
    {
        return $this->passwordHash;
    }

    public function setPasswordHash(string $passwordHash): void
    {
        $this->passwordHash = $passwordHash;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function confirmPhone(string $phone, ?string $ip, ?string $userAgent, string $provider): void
    {
        $this->verifiedPhone = $phone;
        $this->phoneVerifiedAt = new DateTimeImmutable();
        $this->phoneVerifiedIp = $ip;
        $this->phoneVerifiedUserAgent = $userAgent !== null ? mb_substr($userAgent, 0, 255) : null;
        $this->phoneVerificationProvider = $provider;
        $this->updatedAt = new DateTimeImmutable();
    }
}
