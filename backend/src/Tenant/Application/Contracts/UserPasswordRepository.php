<?php

declare(strict_types=1);

namespace App\Tenant\Application\Contracts;

use App\Tenant\Application\Dto\AuthenticatedUserView;

interface UserPasswordRepository
{
    public function authenticate(string $email, string $password): ?AuthenticatedUserView;

    public function changePassword(string $email, string $password): bool;

    public function hasVerifiedPhone(string $email): bool;
}
