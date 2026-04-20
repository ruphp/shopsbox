<?php

declare(strict_types=1);

namespace App\Tenant\Application\Contracts;

use DateTimeImmutable;

interface AuthCodeDelivery
{
    public function deliver(string $channel, string $recipient, string $code, DateTimeImmutable $expiresAt): void;
}
