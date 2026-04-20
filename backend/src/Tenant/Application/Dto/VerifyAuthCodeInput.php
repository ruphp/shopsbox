<?php

declare(strict_types=1);

namespace App\Tenant\Application\Dto;

final readonly class VerifyAuthCodeInput
{
    public function __construct(
        public string $email,
        public string $code,
        public string $phone = '',
        public string $channel = 'email',
        public ?string $ip = null,
        public ?string $userAgent = null,
    ) {
    }
}
