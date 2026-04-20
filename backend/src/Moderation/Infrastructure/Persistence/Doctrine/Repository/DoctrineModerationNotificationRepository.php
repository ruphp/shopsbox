<?php

declare(strict_types=1);

namespace App\Moderation\Infrastructure\Persistence\Doctrine\Repository;

use App\Moderation\Application\Contracts\ModerationNotificationRepository;
use App\Moderation\Infrastructure\Persistence\Doctrine\Entity\ModerationNotification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

final readonly class DoctrineModerationNotificationRepository implements ModerationNotificationRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function createIfMissing(string $itemType, string $itemId, string $reason): void
    {
        $existing = $this->entityManager->createQueryBuilder()
            ->select('notification')
            ->from(ModerationNotification::class, 'notification')
            ->where('notification.itemType = :itemType')
            ->andWhere('notification.itemId = :itemId')
            ->andWhere('notification.status IN (:activeStatuses)')
            ->setParameter('itemType', $itemType)
            ->setParameter('itemId', $itemId)
            ->setParameter('activeStatuses', ['created', 'retry'])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($existing instanceof ModerationNotification) {
            return;
        }

        $this->entityManager->persist(new ModerationNotification(
            Uuid::v7()->toRfc4122(),
            $itemType,
            $itemId,
            $reason,
        ));
    }
}
