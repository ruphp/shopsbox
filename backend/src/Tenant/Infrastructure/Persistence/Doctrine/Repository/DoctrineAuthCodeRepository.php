<?php

declare(strict_types=1);

namespace App\Tenant\Infrastructure\Persistence\Doctrine\Repository;

use App\Tenant\Application\Contracts\AuthCodeRepository;
use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\AuthCode;
use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineAuthCodeRepository implements AuthCodeRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function persist(AuthCode $authCode): void
    {
        $this->entityManager->persist($authCode);
    }

    public function findLatestOpenByEmail(string $email): ?AuthCode
    {
        return $this->findLatestOpenByRecipient('email', $email);
    }

    public function findLatestOpenByRecipient(string $channel, string $recipient): ?AuthCode
    {
        return $this->entityManager->createQueryBuilder()
            ->select('auth_code')
            ->from(AuthCode::class, 'auth_code')
            ->where($channel === 'phone' ? 'auth_code.phone = :recipient' : 'auth_code.email = :recipient')
            ->andWhere('auth_code.channel = :channel')
            ->andWhere('auth_code.consumedAt IS NULL')
            ->setParameter('recipient', $recipient)
            ->setParameter('channel', $channel)
            ->orderBy('auth_code.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function registeredRecipientExists(string $channel, string $recipient): bool
    {
        return $this->entityManager->createQueryBuilder()
            ->select('COUNT(user.id)')
            ->from(User::class, 'user')
            ->where($channel === 'phone' ? 'user.verifiedPhone = :recipient' : 'user.email = :recipient')
            ->setParameter('recipient', $recipient)
            ->getQuery()
            ->getSingleScalarResult() > 0;
    }

    public function countRecentRequestsByRecipient(string $channel, string $recipient, DateTimeImmutable $since): int
    {
        return (int) $this->entityManager->createQueryBuilder()
            ->select('COUNT(auth_code.id)')
            ->from(AuthCode::class, 'auth_code')
            ->where($channel === 'phone' ? 'auth_code.phone = :recipient' : 'auth_code.email = :recipient')
            ->andWhere('auth_code.channel = :channel')
            ->andWhere('auth_code.createdAt >= :since')
            ->setParameter('recipient', $recipient)
            ->setParameter('channel', $channel)
            ->setParameter('since', $since)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
