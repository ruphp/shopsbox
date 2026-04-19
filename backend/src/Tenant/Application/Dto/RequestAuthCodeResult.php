<?php

declare(strict_types=1);

namespace App\Tenant\Application\Dto;

use DateTimeImmutable;

final readonly class RequestAuthCodeResult
{
    public function __construct(
        public string $email,
        public DateTimeImmutable $expiresAt,
    ) {
    }
}
