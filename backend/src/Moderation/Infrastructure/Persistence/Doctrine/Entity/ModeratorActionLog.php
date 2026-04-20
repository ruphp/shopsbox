<?php

declare(strict_types=1);

namespace App\Moderation\Infrastructure\Persistence\Doctrine\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'moderator_action_logs')]
#[ORM\Index(name: 'idx_moderator_action_logs_moderator', columns: ['moderator_id'])]
#[ORM\Index(name: 'idx_moderator_action_logs_item', columns: ['item_type', 'item_id'])]
class ModeratorActionLog
{
    #[ORM\Id]
    #[ORM\Column(type: 'guid')]
    private string $id;

    #[ORM\Column(length: 80)]
    private string $moderatorId;

    #[ORM\Column(length: 32)]
    private string $itemType;

    #[ORM\Column(type: 'guid')]
    private string $itemId;

    #[ORM\Column(length: 32)]
    private string $decision;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $reason;

    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    public function __construct(string $id, string $moderatorId, string $itemType, string $itemId, string $decision, ?string $reason)
    {
        $this->id = $id;
        $this->moderatorId = $moderatorId;
        $this->itemType = $itemType;
        $this->itemId = $itemId;
        $this->decision = $decision;
        $this->reason = $reason;
        $this->createdAt = new DateTimeImmutable();
    }
}
