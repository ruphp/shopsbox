<?php

declare(strict_types=1);

namespace App\Tenant\Infrastructure\Adapters;

use App\Tenant\Application\Contracts\AuthCodeDelivery;
use DateTimeImmutable;

final readonly class DevFileAuthCodeDelivery implements AuthCodeDelivery
{
    public function __construct(private string $projectDir)
    {
    }

    public function deliver(string $email, string $code, DateTimeImmutable $expiresAt): void
    {
        $path = $this->projectDir . '/var/auth-codes.log';
        $line = sprintf(
            "[%s] email=%s code=%s expires_at=%s\n",
            (new DateTimeImmutable())->format(DATE_ATOM),
            $email,
            $code,
            $expiresAt->format(DATE_ATOM),
        );

        file_put_contents($path, $line, FILE_APPEND | LOCK_EX);
    }
}
