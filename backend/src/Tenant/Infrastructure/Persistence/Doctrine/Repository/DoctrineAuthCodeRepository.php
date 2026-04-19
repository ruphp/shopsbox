<?php

declare(strict_types=1);

namespace App\Tenant\Infrastructure\Persistence\Doctrine\Repository;

use App\Tenant\Application\Contracts\AuthCodeRepository;
use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\AuthCode;
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
        return $this->entityManager->createQueryBuilder()
            ->select('auth_code')
            ->from(AuthCode::class, 'auth_code')
            ->where('auth_code.email = :email')
            ->andWhere('auth_code.consumedAt IS NULL')
            ->setParameter('email', $email)
            ->orderBy('auth_code.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
