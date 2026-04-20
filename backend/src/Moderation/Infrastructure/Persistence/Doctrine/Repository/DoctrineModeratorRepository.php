<?php

declare(strict_types=1);

namespace App\Moderation\Infrastructure\Persistence\Doctrine\Repository;

use App\Moderation\Application\Contracts\ModeratorRepository;
use App\Moderation\Application\Dto\ModeratorView;
use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\Role;
use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\User;
use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\UserRoleAssignment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

final readonly class DoctrineModeratorRepository implements ModeratorRepository
{
    private const ROLE_CODE = 'platform_moderator';

    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function listModerators(): array
    {
        $assignments = $this->entityManager->createQueryBuilder()
            ->select('assignment', 'user')
            ->from(UserRoleAssignment::class, 'assignment')
            ->join('assignment.user', 'user')
            ->join('assignment.role', 'role')
            ->where('role.code = :role')
            ->setParameter('role', self::ROLE_CODE)
            ->orderBy('assignment.assignedAt', 'DESC')
            ->getQuery()
            ->getResult();

        return array_map(fn (UserRoleAssignment $assignment): ModeratorView => new ModeratorView(
            $assignment->user()->id(),
            $assignment->user()->email(),
            $assignment->user()->displayName(),
            $assignment->assignedAt()->format('Y-m-d H:i'),
        ), $assignments);
    }

    public function assignByEmail(string $email): bool
    {
        $user = $this->findUser($email);
        $role = $this->findRole();
        if (!$user instanceof User || !$role instanceof Role) {
            return false;
        }

        $existing = $this->findAssignment($user, $role);
        if ($existing instanceof UserRoleAssignment) {
            return true;
        }

        $this->entityManager->persist(new UserRoleAssignment(Uuid::v7()->toRfc4122(), $user, $role, null, null));

        return true;
    }

    public function removeByEmail(string $email): bool
    {
        $user = $this->findUser($email);
        $role = $this->findRole();
        if (!$user instanceof User || !$role instanceof Role) {
            return false;
        }

        $existing = $this->findAssignment($user, $role);
        if (!$existing instanceof UserRoleAssignment) {
            return false;
        }

        $this->entityManager->remove($existing);

        return true;
    }

    private function findUser(string $email): ?User
    {
        return $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
    }

    private function findRole(): ?Role
    {
        return $this->entityManager->getRepository(Role::class)->findOneBy(['code' => self::ROLE_CODE]);
    }

    private function findAssignment(User $user, Role $role): ?UserRoleAssignment
    {
        return $this->entityManager->getRepository(UserRoleAssignment::class)->findOneBy([
            'user' => $user,
            'role' => $role,
            'tenant' => null,
            'store' => null,
        ]);
    }
}
