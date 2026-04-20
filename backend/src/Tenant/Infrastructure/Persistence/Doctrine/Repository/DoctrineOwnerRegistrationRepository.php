<?php

declare(strict_types=1);

namespace App\Tenant\Infrastructure\Persistence\Doctrine\Repository;

use App\Tenant\Application\Contracts\OwnerRegistrationRepository;
use App\Tenant\Application\Dto\RegisterOwnerResult;
use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\Role;
use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\Store;
use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\Tenant;
use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\User;
use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\UserRoleAssignment;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Symfony\Component\Uid\Uuid;

final readonly class DoctrineOwnerRegistrationRepository implements OwnerRegistrationRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function emailExists(string $email): bool
    {
        return $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]) instanceof User;
    }

    public function storeSlugExists(string $slug): bool
    {
        return $this->entityManager->getRepository(Store::class)->findOneBy(['slug' => $slug]) instanceof Store;
    }

    public function register(
        string $tenantId,
        string $storeId,
        string $userId,
        string $ownerName,
        string $email,
        string $phone,
        string $storeName,
        string $storeSlug,
        string $storeDomain,
        string $timezone,
    ): RegisterOwnerResult {
        $tenant = new Tenant($tenantId, $storeName, 'active', $email);
        $store = new Store($storeId, $tenant, $storeName, $storeSlug, $storeDomain, 'draft', 'RUB', $timezone);
        $store->updateSettings($storeName, null, $email, $phone, 'RUB', $timezone);

        $user = new User($userId, $tenant, $email, $ownerName, 'active', false);
        $user->confirmPhone($phone, null, null, 'flash_dev');

        $role = $this->entityManager->getRepository(Role::class)->findOneBy(['code' => 'store_owner']);
        if (!$role instanceof Role) {
            throw new LogicException('Role store_owner must exist before owner registration.');
        }

        $this->entityManager->persist($tenant);
        $this->entityManager->persist($store);
        $this->entityManager->persist($user);
        $this->entityManager->persist(new UserRoleAssignment(Uuid::v7()->toRfc4122(), $user, $role, $tenant, $store));

        return new RegisterOwnerResult($tenantId, $storeId, $userId, $email);
    }
}
