<?php

declare(strict_types=1);

namespace App\Tenant\Application\Dto;

final readonly class ResetPasswordInput
{
    public function __construct(
        public string $email,
        public string $password,
        public string $passwordConfirmation,
    ) {
    }
}
