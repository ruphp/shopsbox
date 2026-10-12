<?php

declare(strict_types=1);

namespace App\Tenant\Infrastructure\Persistence\Doctrine\Repository;

use App\Tenant\Application\Contracts\UserPasswordRepository;
use App\Tenant\Application\Contracts\UserPhoneRepository;
use App\Tenant\Application\Dto\AuthenticatedUserView;
use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class DoctrineUserPasswordRepository implements UserPasswordRepository, UserPhoneRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function authenticate(string $email, string $password): ?AuthenticatedUserView
    {
        $user = $this->findActiveUser($email);
        if (!$user instanceof User || $user->getPassword() === '' || !$this->passwordHasher->isPasswordValid($user, $password)) {
            return null;
        }

        return new AuthenticatedUserView($user->email(), $user->verifiedPhone());
    }

    public function changePassword(string $email, string $password): bool
    {
        $user = $this->findActiveUser($email);
        if (!$user instanceof User) {
            return false;
        }

        $user->setPasswordHash($this->passwordHasher->hashPassword($user, $password));

        return true;
    }

    public function hasVerifiedPhone(string $email): bool
    {
        return (int) $this->entityManager->createQueryBuilder()
            ->select('COUNT(user.id)')
            ->from(User::class, 'user')
            ->where('user.email = :email')
            ->andWhere('user.status = :status')
            ->andWhere('user.verifiedPhone IS NOT NULL')
            ->andWhere('user.phoneVerifiedAt IS NOT NULL')
            ->setParameter('email', strtolower(trim($email)))
            ->setParameter('status', 'active')
            ->getQuery()
            ->getSingleScalarResult() > 0;
    }

    public function confirmForEmail(
        string $email,
        string $phone,
        ?string $ip,
        ?string $userAgent,
        string $provider,
    ): bool {
        $user = $this->findActiveUser(strtolower(trim($email)));
        if (!$user instanceof User) {
            return false;
        }

        $phoneOwner = $this->entityManager->getRepository(User::class)->findOneBy(['verifiedPhone' => $phone]);
        if ($phoneOwner instanceof User && $phoneOwner->email() !== $user->email()) {
            return false;
        }

        $user->confirmPhone($phone, $ip, $userAgent, $provider);

        return true;
    }

    private function findActiveUser(string $email): ?User
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => $email,
            'status' => 'active',
        ]);

        return $user instanceof User ? $user : null;
    }
}
