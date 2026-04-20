<?php

declare(strict_types=1);

namespace App\Tenant\Application\Dto;

final readonly class RequestAuthCodeInput
{
    public function __construct(
        public string $email,
        public string $phone = '',
        public string $channel = 'email',
    ) {
    }
}
