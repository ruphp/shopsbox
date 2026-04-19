<?php

declare(strict_types=1);

namespace App\Tenant\Infrastructure\Persistence\Doctrine\Fixtures;

use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\Role;
use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\Store;
use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\Tenant;
use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\User;
use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\UserRoleAssignment;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;

final class DemoTenantFixtures extends Fixture
{
    public const DEMO_TENANT_REFERENCE = 'demo-tenant';
    public const DEMO_STORE_REFERENCE = 'demo-store';

    private const DEMO_TENANT_ID = '11111111-1111-4111-8111-111111111111';
    private const DEMO_STORE_ID = '22222222-2222-4222-8222-222222222222';

    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $tenant = $manager->find(Tenant::class, self::DEMO_TENANT_ID);
        if (!$tenant instanceof Tenant) {
            $tenant = new Tenant(
                self::DEMO_TENANT_ID,
                'Demo Tenant',
                'active',
                'billing@demo.shopsbox.local',
            );

            $manager->persist($tenant);
        }

        $store = $manager->find(Store::class, self::DEMO_STORE_ID);
        if (!$store instanceof Store) {
            $store = new Store(
                self::DEMO_STORE_ID,
                $tenant,
                'Demo Store',
                'demo-store',
                'demo.shopsbox.local',
                'active',
                'RUB',
                'Asia/Yekaterinburg',
            );

            $manager->persist($store);
        }

        $this->addReference(self::DEMO_TENANT_REFERENCE, $tenant);
        $this->addReference(self::DEMO_STORE_REFERENCE, $store);

        $roles = $this->createRoles();
        foreach ($roles as $code => $role) {
            $existingRole = $manager->find(Role::class, $role->id());
            if ($existingRole instanceof Role) {
                $roles[$code] = $existingRole;
                continue;
            }

            $manager->persist($roles[$code]);
        }

        foreach ($this->demoUsers() as $userData) {
            if ($manager->find(User::class, $userData['id']) instanceof User) {
                continue;
            }

            $role = $roles[$userData['role']];
            $roleTenant = $userData['platform'] ? null : $tenant;
            $roleStore = $userData['store_role'] ? $store : null;
            $userTenant = $userData['platform'] ? null : $tenant;

            $user = new User(
                $userData['id'],
                $userTenant,
                $userData['email'],
                $userData['name'],
                'active',
                true,
            );
            $user->setPasswordHash($this->passwordHasher->hashPassword($user, $userData['password']));

            $manager->persist($user);
            $manager->persist(new UserRoleAssignment(
                $this->uuid(),
                $user,
                $role,
                $roleTenant,
                $roleStore,
            ));
        }

        $manager->flush();
    }

    /**
     * @return array<string, Role>
     */
    private function createRoles(): array
    {
        $roles = [
            ['33333333-0001-4333-8333-333333333333', 'platform_admin', 'Platform Admin', 'platform', ['platform.tenants.manage', 'platform.stores.manage', 'platform.ops.manage']],
            ['33333333-0002-4333-8333-333333333333', 'platform_operator', 'Platform Operator', 'platform', ['platform.stores.manage', 'platform.ops.manage']],
            ['33333333-0003-4333-8333-333333333333', 'store_owner', 'Store Owner', 'store', ['store.settings.manage', 'store.users.manage', 'store.products.manage', 'store.inventory.manage', 'store.orders.manage', 'store.customers.manage', 'store.storefront.manage', 'store.files.manage', 'store.backups.request', 'store.billing.manage', 'store.audit.view']],
            ['33333333-0004-4333-8333-333333333333', 'store_manager', 'Store Manager', 'store', ['store.settings.manage', 'store.products.manage', 'store.inventory.manage', 'store.orders.manage', 'store.customers.manage', 'store.storefront.manage', 'store.files.manage', 'store.audit.view']],
            ['33333333-0005-4333-8333-333333333333', 'order_manager', 'Order Manager', 'store', ['store.orders.manage', 'store.customers.manage']],
            ['33333333-0006-4333-8333-333333333333', 'catalog_manager', 'Catalog Manager', 'store', ['store.products.manage', 'store.storefront.manage', 'store.files.manage']],
            ['33333333-0007-4333-8333-333333333333', 'inventory_manager', 'Inventory Manager', 'store', ['store.inventory.manage']],
            ['33333333-0008-4333-8333-333333333333', 'support_agent', 'Support Agent', 'support', ['support.tickets.manage', 'store.audit.view']],
            ['33333333-0009-4333-8333-333333333333', 'customer', 'Customer', 'customer', []],
        ];

        $result = [];
        foreach ($roles as [$id, $code, $name, $scope, $permissions]) {
            $result[$code] = new Role($id, $code, $name, $scope, $permissions);
        }

        return $result;
    }

    /**
     * @return list<array{id: string, role: string, email: string, password: string, name: string, platform: bool, store_role: bool}>
     */
    private function demoUsers(): array
    {
        return [
            ['id' => '44444444-0001-4444-8444-444444444444', 'role' => 'platform_admin', 'email' => 'platform-admin@shopsbox.local', 'password' => 'dev-platform-admin-ChangeMe-2026', 'name' => 'Platform Admin', 'platform' => true, 'store_role' => false],
            ['id' => '44444444-0002-4444-8444-444444444444', 'role' => 'platform_operator', 'email' => 'platform-operator@shopsbox.local', 'password' => 'dev-platform-operator-ChangeMe-2026', 'name' => 'Platform Operator', 'platform' => true, 'store_role' => false],
            ['id' => '44444444-0003-4444-8444-444444444444', 'role' => 'store_owner', 'email' => 'owner@demo.shopsbox.local', 'password' => 'dev-store-owner-ChangeMe-2026', 'name' => 'Store Owner', 'platform' => false, 'store_role' => true],
            ['id' => '44444444-0004-4444-8444-444444444444', 'role' => 'store_manager', 'email' => 'manager@demo.shopsbox.local', 'password' => 'dev-store-manager-ChangeMe-2026', 'name' => 'Store Manager', 'platform' => false, 'store_role' => true],
            ['id' => '44444444-0005-4444-8444-444444444444', 'role' => 'order_manager', 'email' => 'orders@demo.shopsbox.local', 'password' => 'dev-orders-ChangeMe-2026', 'name' => 'Order Manager', 'platform' => false, 'store_role' => true],
            ['id' => '44444444-0006-4444-8444-444444444444', 'role' => 'catalog_manager', 'email' => 'catalog@demo.shopsbox.local', 'password' => 'dev-catalog-ChangeMe-2026', 'name' => 'Catalog Manager', 'platform' => false, 'store_role' => true],
            ['id' => '44444444-0007-4444-8444-444444444444', 'role' => 'inventory_manager', 'email' => 'inventory@demo.shopsbox.local', 'password' => 'dev-inventory-ChangeMe-2026', 'name' => 'Inventory Manager', 'platform' => false, 'store_role' => true],
            ['id' => '44444444-0008-4444-8444-444444444444', 'role' => 'support_agent', 'email' => 'support@shopsbox.local', 'password' => 'dev-support-ChangeMe-2026', 'name' => 'Support Agent', 'platform' => true, 'store_role' => false],
            ['id' => '44444444-0009-4444-8444-444444444444', 'role' => 'customer', 'email' => 'customer@demo.shopsbox.local', 'password' => 'dev-customer-ChangeMe-2026', 'name' => 'Customer', 'platform' => false, 'store_role' => true],
        ];
    }

    private function uuid(): string
    {
        return Uuid::v7()->toRfc4122();
    }
}
