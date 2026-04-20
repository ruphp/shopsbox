<?php

declare(strict_types=1);

namespace App\Moderation\Infrastructure\Persistence\Doctrine\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'moderation_notifications')]
#[ORM\UniqueConstraint(name: 'uniq_moderation_notifications_active', columns: ['item_type', 'item_id', 'status'])]
#[ORM\Index(name: 'idx_moderation_notifications_status', columns: ['status'])]
class ModerationNotification
{
    #[ORM\Id]
    #[ORM\Column(type: 'guid')]
    private string $id;

    #[ORM\Column(length: 32)]
    private string $itemType;

    #[ORM\Column(type: 'guid')]
    private string $itemId;

    #[ORM\Column(length: 32)]
    private string $status = 'created';

    #[ORM\Column(length: 120)]
    private string $reason;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $sentAt = null;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $failedAt = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $errorMessage = null;

    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    public function __construct(string $id, string $itemType, string $itemId, string $reason)
    {
        $this->id = $id;
        $this->itemType = $itemType;
        $this->itemId = $itemId;
        $this->reason = $reason;
        $this->createdAt = new DateTimeImmutable();
    }
}
