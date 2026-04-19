<?php

declare(strict_types=1);

namespace App\Tenant\Application\Contracts;

use App\Tenant\Infrastructure\Persistence\Doctrine\Entity\AuthCode;

interface AuthCodeRepository
{
    public function persist(AuthCode $authCode): void;

    public function findLatestOpenByEmail(string $email): ?AuthCode;
}
