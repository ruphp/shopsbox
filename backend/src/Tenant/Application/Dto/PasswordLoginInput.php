<?php

declare(strict_types=1);

namespace App\Tenant\Application\Dto;

final readonly class PasswordLoginInput
{
    public function __construct(
        public string $email,
        public string $password,
    ) {
    }
}
