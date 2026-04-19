<?php

declare(strict_types=1);

namespace App\Tenant\Infrastructure\Persistence\Doctrine\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'auth_codes')]
#[ORM\Index(name: 'idx_auth_codes_email_created', columns: ['email', 'created_at'])]
class AuthCode
{
    #[ORM\Id]
    #[ORM\Column(type: 'guid')]
    private string $id;

    #[ORM\Column(length: 180)]
    private string $email;

    #[ORM\Column(length: 64)]
    private string $codeHash;

    #[ORM\Column]
    private DateTimeImmutable $expiresAt;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $consumedAt = null;

    #[ORM\Column]
    private int $attempts = 0;

    #[ORM\Column]
    private int $maxAttempts;

    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    public function __construct(
        string $id,
        string $email,
        string $codeHash,
        DateTimeImmutable $expiresAt,
        int $maxAttempts,
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->codeHash = $codeHash;
        $this->expiresAt = $expiresAt;
        $this->maxAttempts = $maxAttempts;
        $this->createdAt = new DateTimeImmutable();
    }

    public function matches(string $code): bool
    {
        return hash_equals($this->codeHash, hash('sha256', $code));
    }

    public function isExpired(): bool
    {
        return $this->expiresAt <= new DateTimeImmutable();
    }

    public function hasAttemptsLeft(): bool
    {
        return $this->attempts < $this->maxAttempts;
    }

    public function recordFailedAttempt(): void
    {
        ++$this->attempts;
    }

    public function consume(): void
    {
        $this->consumedAt = new DateTimeImmutable();
    }
}
