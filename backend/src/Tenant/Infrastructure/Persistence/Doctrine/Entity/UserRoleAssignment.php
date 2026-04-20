<?php

declare(strict_types=1);

namespace App\Tenant\Infrastructure\Persistence\Doctrine\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'user_roles')]
#[ORM\Index(name: 'idx_user_roles_user', columns: ['user_id'])]
#[ORM\Index(name: 'idx_user_roles_tenant', columns: ['tenant_id'])]
#[ORM\Index(name: 'idx_user_roles_store', columns: ['store_id'])]
#[ORM\UniqueConstraint(name: 'uniq_user_roles_scope', columns: ['user_id', 'role_id', 'tenant_id', 'store_id'])]
class UserRoleAssignment
{
    #[ORM\Id]
    #[ORM\Column(type: 'guid')]
    private string $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Role::class)]
    #[ORM\JoinColumn(name: 'role_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Role $role;

    #[ORM\ManyToOne(targetEntity: Tenant::class)]
    #[ORM\JoinColumn(name: 'tenant_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    private ?Tenant $tenant;

    #[ORM\ManyToOne(targetEntity: Store::class)]
    #[ORM\JoinColumn(name: 'store_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    private ?Store $store;

    #[ORM\Column]
    private DateTimeImmutable $assignedAt;

    public function __construct(string $id, User $user, Role $role, ?Tenant $tenant, ?Store $store)
    {
        $this->id = $id;
        $this->user = $user;
        $this->role = $role;
        $this->tenant = $tenant;
        $this->store = $store;
        $this->assignedAt = new DateTimeImmutable();
    }

    public function user(): User
    {
        return $this->user;
    }

    public function role(): Role
    {
        return $this->role;
    }

    public function assignedAt(): DateTimeImmutable
    {
        return $this->assignedAt;
    }
}
