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

    #[ORM\Column(length: 40, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 16)]
    private string $channel = 'email';

    #[ORM\Column(length: 64)]
    private string $codeHash;

    #[ORM\Column]
    private DateTimeImmutable $expiresAt;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $consumedAt = null;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $verifiedAt = null;

    #[ORM\Column(length: 45, nullable: true)]
    private ?string $verifiedIp = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $verifiedUserAgent = null;

    #[ORM\Column(length: 40)]
    private string $provider = 'flash_dev';

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
        ?string $phone = null,
        string $channel = 'email',
        string $provider = 'flash_dev',
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->phone = $phone;
        $this->channel = $channel;
        $this->codeHash = $codeHash;
        $this->expiresAt = $expiresAt;
        $this->maxAttempts = $maxAttempts;
        $this->provider = $provider;
        $this->createdAt = new DateTimeImmutable();
    }

    public function email(): string
    {
        return $this->email;
    }

    public function phone(): ?string
    {
        return $this->phone;
    }

    public function channel(): string
    {
        return $this->channel;
    }

    public function recipient(): string
    {
        return $this->channel === 'phone' ? (string) $this->phone : $this->email;
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

    public function consume(?string $ip = null, ?string $userAgent = null): void
    {
        $now = new DateTimeImmutable();

        $this->consumedAt = $now;
        $this->verifiedAt = $now;
        $this->verifiedIp = $ip;
        $this->verifiedUserAgent = $userAgent !== null ? mb_substr($userAgent, 0, 255) : null;
    }
}
