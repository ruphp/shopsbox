<?php

declare(strict_types=1);

namespace App\Tenant\Application\Contracts;

use DateTimeImmutable;

interface AuthCodeDelivery
{
    public function deliver(string $email, string $code, DateTimeImmutable $expiresAt): void;
}
