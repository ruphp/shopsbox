<?php

declare(strict_types=1);

namespace App\Tenant\Infrastructure\Persistence\Doctrine\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'roles')]
#[ORM\UniqueConstraint(name: 'uniq_roles_code', columns: ['code'])]
class Role
{
    #[ORM\Id]
    #[ORM\Column(type: 'guid')]
    private string $id;

    #[ORM\Column(length: 64)]
    private string $code;

    #[ORM\Column(length: 120)]
    private string $name;

    #[ORM\Column(length: 32)]
    private string $scope;

    /**
     * @var list<string>
     */
    #[ORM\Column(type: 'json')]
    private array $permissions;

    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    public function __construct(string $id, string $code, string $name, string $scope, array $permissions)
    {
        $this->id = $id;
        $this->code = $code;
        $this->name = $name;
        $this->scope = $scope;
        $this->permissions = array_values($permissions);
        $this->createdAt = new DateTimeImmutable();
    }

    public function id(): string
    {
        return $this->id;
    }

    public function code(): string
    {
        return $this->code;
    }
}
