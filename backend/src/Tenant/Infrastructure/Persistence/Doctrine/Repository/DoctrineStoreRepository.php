<?php

declare(strict_types=1);

namespace App\Tenant\Infrastructure\Persistence\Doctrine\Repository;

use App\Tenant\Application\Contracts\StoreRepository;
use App\Tenant\Application\Dto\StoreSettingsView;
use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\Store;
use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\Tenant;
use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\User;
use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\UserRoleAssignment;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;

final class DoctrineStoreRepository implements StoreRepository
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function existsByDomain(string $domain): bool
    {
        return $this->entityManager->createQueryBuilder()
            ->select('COUNT(store.id)')
            ->from(Store::class, 'store')
            ->where('store.domain = :domain')
            ->setParameter('domain', $domain)
            ->getQuery()
            ->getSingleScalarResult() > 0;
    }

    public function findSettingsByOwnerEmail(string $email): ?StoreSettingsView
    {
        $store = $this->findStoreByOwnerEmail($email);

        return $store instanceof Store ? $this->toSettingsView($store) : null;
    }

    public function updateSettingsForOwnerEmail(
        string $email,
        string $name,
        ?string $publicDescription,
        ?string $contactEmail,
        ?string $contactPhone,
        string $defaultCurrency,
        string $timezone,
        array $themeSettings,
    ): ?StoreSettingsView {
        $store = $this->findStoreByOwnerEmail($email);
        if (!$store instanceof Store) {
            return null;
        }

        $store->updateSettings($name, $publicDescription, $contactEmail, $contactPhone, $defaultCurrency, $timezone);
        $store->updateThemeSettings($themeSettings);

        return $this->toSettingsView($store);
    }

    public function existsByPublicSubdomain(string $publicSubdomain, string $exceptStoreId): bool
    {
        return $this->entityManager->createQueryBuilder()
            ->select('COUNT(store.id)')
            ->from(Store::class, 'store')
            ->where('store.publicSubdomain = :publicSubdomain')
            ->andWhere('store.id != :exceptStoreId')
            ->setParameter('publicSubdomain', $publicSubdomain)
            ->setParameter('exceptStoreId', $exceptStoreId)
            ->getQuery()
            ->getSingleScalarResult() > 0;
    }

    public function updatePublicationForOwnerEmail(
        string $email,
        string $ownerName,
        string $publicationEmail,
        string $phone,
        string $publicSubdomain,
        bool $termsAccepted,
    ): ?StoreSettingsView {
        $store = $this->findStoreByOwnerEmail($email);
        if (!$store instanceof Store) {
            return null;
        }

        $store->updatePublicationRequest($ownerName, $publicationEmail, $phone, $publicSubdomain, $termsAccepted);

        return $this->toSettingsView($store);
    }

    public function persist(
        string $id,
        string $tenantId,
        string $name,
        string $slug,
        string $domain,
        string $status,
        string $defaultCurrency,
        string $timezone,
    ): void
    {
        $tenant = $this->entityManager->getReference(Tenant::class, $tenantId);
        if (!$tenant instanceof Tenant) {
            throw new LogicException('Tenant reference must be a Tenant entity.');
        }

        $store = new Store(
            $id,
            $tenant,
            $name,
            $slug,
            $domain,
            $status,
            $defaultCurrency,
            $timezone,
        );

        $this->entityManager->persist($store);
    }

    private function findStoreByOwnerEmail(string $email): ?Store
    {
        return $this->entityManager->createQueryBuilder()
            ->select('store')
            ->from(Store::class, 'store')
            ->innerJoin(UserRoleAssignment::class, 'assignment', 'WITH', 'assignment.store = store')
            ->innerJoin(User::class, 'user', 'WITH', 'assignment.user = user')
            ->where('user.email = :email')
            ->andWhere('store.status = :status')
            ->setParameter('email', $email)
            ->setParameter('status', 'active')
            ->orderBy('store.createdAt', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    private function toSettingsView(Store $store): StoreSettingsView
    {
        return new StoreSettingsView(
            $store->tenant()->id(),
            $store->id(),
            $store->name(),
            $store->slug(),
            $store->domain(),
            $store->status(),
            $store->defaultCurrency(),
            $store->timezone(),
            $store->publicDescription(),
            $store->contactEmail(),
            $store->contactPhone(),
            $store->themeSettings(),
            $store->publicationOwnerName(),
            $store->publicationEmail(),
            $store->publicationPhone(),
            $store->publicSubdomain(),
            $store->publicationStatus(),
            $store->publicationTermsAcceptedAt()?->format('Y-m-d H:i'),
        );
    }
}
